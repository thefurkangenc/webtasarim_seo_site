<?php

namespace App\Services\Sitemap;

use App\Jobs\GenerateSitemapJob;
use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Service\Service;
use App\Services\Setting\SettingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use XMLWriter;

/**
 * Site haritası (sitemap.xml) üretimi ve panel ayarları.
 *
 * Dosyalar storage/app/{config('sitemap.path')} altına XMLWriter ile
 * akış halinde yazılır (binlerce bölge sayfası olabileceğinden hepsini
 * belleğe almadan). `sitemap.xml` bir indekstir, her kaynak kendi dosyasında.
 */
class SitemapService
{
    public function __construct(private readonly SettingService $settings) {}

    /** Panel sayfasının (tek form) ihtiyaç duyduğu her şey. */
    public function formData(): array
    {
        return [
            'sources' => config('sitemap.sources'),
            'enabled' => $this->enabledSources(),
            'excludedUrls' => (string) $this->settings->get('sitemap', 'excluded_urls', ''),
            'extraUrls' => (string) $this->settings->get('sitemap', 'extra_urls', ''),
            'robotsBody' => $this->robotsBody(),
            'report' => $this->report(),
        ];
    }

    /**
     * Ön yüzde sunulan robots.txt'nin tam içeriği: panelden düzenlenen gövde +
     * sonuna eklenen `Sitemap:` satırı. Adres `route()` ile üretilir, bu yüzden
     * alan adı hiçbir dosyada sabit durmaz — site hangi domainde çalışıyorsa
     * robots.txt de onu gösterir.
     */
    public function robotsTxt(): string
    {
        $body = rtrim($this->robotsBody());
        $line = 'Sitemap: '.route('sitemap.index');

        // Kullanıcı gövdeye elle bir Sitemap satırı yazdıysa tekrar eklenmez.
        return str_contains(strtolower($body), 'sitemap:')
            ? $body."\n"
            : $body."\n\n".$line."\n";
    }

    /** Panelde düzenlenen gövde; hiç kaydedilmediyse config'teki varsayılan. */
    public function robotsBody(): string
    {
        $saved = (string) $this->settings->get('sitemap', 'robots_txt', '');

        return trim($saved) !== '' ? $saved : config('sitemap.robots_default');
    }

    /** Son üretimin özeti — kaynak başına URL sayısı, toplam, zaman. Henüz üretilmediyse null. */
    public function report(): ?array
    {
        $report = Cache::get('sitemap.report');

        if ($report) {
            // Cache'te Carbon nesnesi değil ISO metni tutulur — süreçler arası
            // (komut ↔ web isteği) unserialize sorunlarından bu şekilde kaçınılır.
            $report['generated_at'] = Carbon::parse($report['generated_at']);
        }

        return $report;
    }

    /** @param  array<string, mixed>  $data */
    public function update(array $data): void
    {
        $sources = collect(array_keys(config('sitemap.sources')))
            ->filter(fn (string $key) => filter_var($data["source_{$key}"] ?? false, FILTER_VALIDATE_BOOLEAN))
            ->implode(',');

        $this->settings->putGroup('sitemap', [
            'sources' => $sources,
            'excluded_urls' => $data['excluded_urls'] ?? '',
            'extra_urls' => $data['extra_urls'] ?? '',
            'robots_txt' => $data['robots_txt'] ?? '',
        ]);

        $this->regenerate();
    }

    /** Panelde "Şimdi Yeniden Oluştur" — kuyruğa atar, sayfayı bloklamaz. */
    public function regenerate(): void
    {
        GenerateSitemapJob::dispatch();
    }

    /** Gerçek üretim — komut ve kuyruk işi burayı çağırır. */
    public function generate(): array
    {
        Storage::disk('local')->makeDirectory(config('sitemap.path'));

        $enabled = $this->enabledSources();
        $sources = [
            'static' => $enabled['static'] ? $this->writeStatic() : $this->disable('static'),
            'pages' => $enabled['pages'] ? $this->writePages() : $this->disable('pages'),
            'blog' => $enabled['blog'] ? $this->writeBlog() : $this->disable('blog'),
            'services' => $enabled['services'] ? $this->writeServices() : $this->disable('services'),
            'regions' => $enabled['regions'] ? $this->writeRegions() : $this->disable('regions'),
            'extra' => $this->writeExtra(),
        ];

        $this->writeIndex($sources);

        $report = [
            'generated_at' => now()->toIso8601String(),
            'sources' => $sources,
            'total' => array_sum(array_column($sources, 'count')),
        ];

        Cache::forever('sitemap.report', $report);

        return $report;
    }

    /** Raw dosya içeriği — ön yüz controller'ı bunu sunar, yoksa null. */
    public function read(string $filename): ?string
    {
        $path = config('sitemap.path').'/'.$filename;

        return Storage::disk('local')->exists($path) ? Storage::disk('local')->get($path) : null;
    }

    /** @return array<string, bool> */
    private function enabledSources(): array
    {
        $all = array_keys(config('sitemap.sources'));
        $raw = (string) $this->settings->get('sitemap', 'sources', implode(',', $all));
        $on = array_flip(array_filter(explode(',', $raw)));

        return collect($all)->mapWithKeys(fn ($key) => [$key => isset($on[$key])])->all();
    }

    /** @return array{files: array<int, string>, count: int} */
    private function writeStatic(): array
    {
        $urls = collect(config('sitemap.static_routes'))
            ->map(fn (string $name) => ['loc' => route($name), 'lastmod' => null, 'image' => null])
            ->all();

        return $this->writeSource('static', $urls);
    }

    /** @return array{files: array<int, string>, count: int} */
    private function writePages(): array
    {
        $urls = [];

        Page::query()->visible()->with('seo')->chunk(100, function ($pages) use (&$urls) {
            foreach ($pages as $page) {
                if ($this->isNoindex($page)) {
                    continue;
                }

                $urls[] = ['loc' => $page->url(), 'lastmod' => $page->updated_at, 'image' => $this->coverImage($page)];
            }
        });

        return $this->writeSource('pages', $urls);
    }

    /** @return array{files: array<int, string>, count: int} */
    private function writeBlog(): array
    {
        $urls = [];

        Blog::query()->where('status', Blog::STATUS_PUBLISHED)->with('seo')
            ->chunk(100, function ($posts) use (&$urls) {
                foreach ($posts as $post) {
                    if ($this->isNoindex($post)) {
                        continue;
                    }

                    $urls[] = ['loc' => $post->publicUrl(), 'lastmod' => $post->updated_at, 'image' => $this->coverImage($post)];
                }
            });

        return $this->writeSource('blog', $urls);
    }

    /** @return array{files: array<int, string>, count: int} */
    private function writeServices(): array
    {
        $urls = [];

        Service::query()->where('status', Service::STATUS_PUBLISHED)->with('seo')
            ->chunk(100, function ($services) use (&$urls) {
                foreach ($services as $service) {
                    if ($this->isNoindex($service)) {
                        continue;
                    }

                    $urls[] = ['loc' => $service->publicUrl(), 'lastmod' => $service->updated_at, 'image' => $this->coverImage($service)];
                }
            });

        return $this->writeSource('services', $urls);
    }

    /** Hizmet × bölge sayfaları — tek hizmet onlarca bölgeye bağlı olabilir. */
    private function writeRegions(): array
    {
        $urls = [];

        Service::query()->where('status', Service::STATUS_PUBLISHED)
            ->with(['seo', 'regions' => fn ($query) => $query->where('is_active', true)])
            ->chunk(50, function ($services) use (&$urls) {
                foreach ($services as $service) {
                    if ($this->isNoindex($service)) {
                        continue;
                    }

                    foreach ($service->regions as $region) {
                        $urls[] = [
                            'loc' => route('hizmetler.show-region', [$service->slug, $region->slug]),
                            'lastmod' => $service->updated_at?->greaterThan($region->updated_at) ? $service->updated_at : $region->updated_at,
                            'image' => $this->coverImage($service),
                        ];
                    }
                }
            });

        return $this->writeSource('regions', $urls);
    }

    /** Panelde elle eklenen ek adresler — kaynak anahtarı yok, her zaman denenir. */
    private function writeExtra(): array
    {
        $urls = $this->lines('extra_urls')
            ->map(fn (string $line) => ['loc' => $line, 'lastmod' => null, 'image' => null])
            ->all();

        return $this->writeSource('extra', $urls);
    }

    /**
     * Bir kaynağın URL listesini hariç tutulanlardan temizler, eski dosyalarını
     * siler, 50.000'i aşarsa birden çok dosyaya böler ve yazar.
     *
     * @param  array<int, array{loc: string, lastmod: ?Carbon, image: ?string}>  $urls
     * @return array{files: array<int, string>, count: int}
     */
    private function writeSource(string $key, array $urls): array
    {
        $this->deleteFiles($key);

        $urls = $this->excludeUrls($urls);

        if ($urls === []) {
            return ['files' => [], 'count' => 0];
        }

        $chunks = array_chunk($urls, config('sitemap.max_urls_per_file'));
        $files = [];

        foreach ($chunks as $index => $chunk) {
            $file = count($chunks) > 1 ? "sitemap-{$key}-{$index}.xml" : "sitemap-{$key}.xml";
            $this->writeUrlset($file, $chunk);
            $files[] = $file;
        }

        return ['files' => $files, 'count' => count($urls)];
    }

    /** Kaynak kapalıysa eski dosyalarını sil, rapora boş gir. */
    private function disable(string $key): array
    {
        $this->deleteFiles($key);

        return ['files' => [], 'count' => 0];
    }

    private function deleteFiles(string $key): void
    {
        $disk = Storage::disk('local');
        $prefix = config('sitemap.path').'/'."sitemap-{$key}";

        foreach ($disk->files(config('sitemap.path')) as $path) {
            if (str_starts_with($path, $prefix)) {
                $disk->delete($path);
            }
        }
    }

    /** @param  array<int, array{loc: string}>  $urls */
    private function excludeUrls(array $urls): array
    {
        $excluded = $this->excludedPaths();

        if ($excluded === []) {
            return $urls;
        }

        return array_values(array_filter(
            $urls,
            fn (array $url) => ! in_array($this->pathOf($url['loc']), $excluded, true),
        ));
    }

    /** @return array<int, string> */
    private function excludedPaths(): array
    {
        return $this->lines('excluded_urls')->map(fn (string $line) => $this->pathOf($line))->unique()->values()->all();
    }

    private function pathOf(string $url): string
    {
        return rtrim((string) parse_url($url, PHP_URL_PATH), '/') ?: '/';
    }

    /** Ayar metnini satır satır, boşları atarak döner. */
    private function lines(string $key): Collection
    {
        $raw = (string) $this->settings->get('sitemap', $key, '');

        return collect(preg_split('/\r?\n/', $raw))->map(fn ($line) => trim((string) $line))->filter()->values();
    }

    private function isNoindex(Model $model): bool
    {
        return $model->seo && ! $model->seo->robots_index;
    }

    private function coverImage(Model $model): ?string
    {
        return method_exists($model, 'mediaUrl') ? $model->mediaUrl('cover', 'medium') : null;
    }

    /** @param  array<int, array{loc: string, lastmod: ?Carbon, image: ?string}>  $urls */
    private function writeUrlset(string $file, array $urls): void
    {
        $writer = new XMLWriter;
        $writer->openUri($this->diskPath($file));
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);
        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $writer->writeAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');

        foreach ($urls as $url) {
            $writer->startElement('url');
            $writer->writeElement('loc', $url['loc']);

            if ($url['lastmod'] instanceof Carbon) {
                $writer->writeElement('lastmod', $url['lastmod']->toAtomString());
            }

            if (filled($url['image']) && config('sitemap.image_sitemap')) {
                $writer->startElement('image:image');
                $writer->writeElement('image:loc', $url['image']);
                $writer->endElement();
            }

            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();
        $writer->flush();
    }

    /** @param  array<string, array{files: array<int, string>, count: int}>  $sources */
    private function writeIndex(array $sources): void
    {
        $writer = new XMLWriter;
        $writer->openUri($this->diskPath('sitemap.xml'));
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);
        $writer->startElement('sitemapindex');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $now = now()->toAtomString();

        foreach ($sources as $source) {
            foreach ($source['files'] as $file) {
                $writer->startElement('sitemap');
                $writer->writeElement('loc', rtrim(config('app.url'), '/')."/{$file}");
                $writer->writeElement('lastmod', $now);
                $writer->endElement();
            }
        }

        $writer->endElement();
        $writer->endDocument();
        $writer->flush();
    }

    private function diskPath(string $file): string
    {
        return Storage::disk('local')->path(config('sitemap.path').'/'.$file);
    }
}
