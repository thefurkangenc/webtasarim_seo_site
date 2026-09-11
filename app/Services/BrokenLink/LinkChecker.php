<?php

namespace App\Services\BrokenLink;

use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Service\Service;
use App\Services\Redirect\RedirectResolver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Tek bir adresin çalışıp çalışmadığını söyler.
 *
 * İç adresler için ağ isteği YOKTUR: route tablosuna bakılır, dinamik
 * adreslerde kaydın varlığı ve yayın durumu sorgulanır. Böylece yüzlerce
 * iç link saniyeler içinde denetlenir. Dış adreslerde HTTP isteği atılır.
 *
 * Nesne tarama boyunca yaşar ve sonuçları hatırlar — aynı adres on ayrı
 * içerikte geçse de bir kez kontrol edilir.
 */
class LinkChecker
{
    /** @var array<string, array<string, mixed>|null> */
    private array $cache = [];

    public function __construct(private readonly RedirectResolver $redirects) {}

    /**
     * Adres sağlamsa null; kırıksa sebebiyle birlikte döner.
     *
     * @return array{scope: string, status_code: int|null, reason: string, message: string|null}|null
     */
    public function check(string $url): ?array
    {
        $url = trim($url);

        // Sayfa içi çapa, boş href ve mailto/tel gibi şemalar denetlenmez.
        if ($url === '' || str_starts_with($url, '#')) {
            return null;
        }

        $scheme = Str::lower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme !== '' && ! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        if (! array_key_exists($url, $this->cache)) {
            $this->cache[$url] = $this->inspect($url);
        }

        return $this->cache[$url];
    }

    /** @return array{scope: string, status_code: int|null, reason: string, message: string|null}|null */
    private function inspect(string $url): ?array
    {
        // "//example.com/x" tarayıcıda sayfanın şemasını devralır.
        $normalized = str_starts_with($url, '//') ? 'https:'.$url : $url;
        $parts = parse_url($normalized);

        if ($parts === false) {
            return $this->broken('internal', null, 'invalid', 'Adres okunamadı.');
        }

        $host = Str::lower($parts['host'] ?? '');

        return $host === '' || $this->isOwnHost($host)
            ? $this->checkInternal($parts['path'] ?? '/')
            : $this->checkExternal($normalized, $host);
    }

    /**
     * İç adres: önce sunucudaki gerçek dosyalar, sonra yönlendirmeler, en son
     * route tablosu. Dinamik route'larda kaydın kendisi sorgulanır — catch-all
     * `/{path}` her şeye uyduğu için tek başına "çalışıyor" demek değildir.
     */
    private function checkInternal(string $path): ?array
    {
        $path = rawurldecode($path);
        $trimmed = trim($path, '/');

        if ($trimmed === '') {
            return null;
        }

        // Yüklenen dosyalar ve statik varlıklar (/storage/..., /assets/...).
        if (str_starts_with($trimmed, 'storage/')) {
            return Storage::disk('public')->exists(Str::after($trimmed, 'storage/'))
                ? null
                : $this->broken('internal', null, 'missing_file', 'Dosya medya deposunda bulunamadı.');
        }

        if (is_file(public_path($trimmed))) {
            return null;
        }

        // Yönlendirme yöneticisi bu adresi karşılıyorsa link kırık değildir.
        if ($this->redirects->resolve($trimmed)) {
            return null;
        }

        try {
            $route = Route::getRoutes()->match(Request::create('/'.$trimmed, 'GET'));
        } catch (HttpException) {
            return $this->broken('internal', 404, 'not_found', 'Bu adrese karşılık gelen bir sayfa yok.');
        } catch (Throwable) {
            return $this->broken('internal', null, 'unresolved', 'Adres çözümlenemedi.');
        }

        return match ($route->getName()) {
            'sayfa.show' => $this->checkRecord(Page::where('path', $trimmed)->first(), 'Sayfa'),
            'blog.show' => $this->checkRecord(Blog::where('slug', $route->parameter('slug'))->first(), 'Blog yazısı'),
            'hizmetler.show' => $this->checkRecord(Service::where('slug', $route->parameter('slug'))->first(), 'Hizmet'),
            'hizmetler.show-region' => $this->checkRegion($route->parameter('slug'), $route->parameter('region')),
            default => null,
        };
    }

    /**
     * Kayıt var mı, yayında mı? Taslak/yayından kaldırılmış bir kayda verilen
     * link ziyaretçide 404 üretir — ayrı bir sebeple raporlanır ki düzeltme
     * yolu ("yayınla" mı, "linki değiştir" mi) belli olsun.
     */
    private function checkRecord(Page|Blog|Service|null $record, string $label): ?array
    {
        if (! $record) {
            return $this->broken('internal', 404, 'not_found', "{$label} bulunamadı.");
        }

        return $record->publicUrl()
            ? null
            : $this->broken('internal', 404, 'unpublished', "{$label} yayında değil, adres ziyaretçiye 404 döner.");
    }

    private function checkRegion(?string $slug, ?string $regionSlug): ?array
    {
        $service = Service::where('slug', $slug)->first();

        if ($result = $this->checkRecord($service, 'Hizmet')) {
            return $result;
        }

        return $service->regions()->where('slug', $regionSlug)->where('is_active', true)->exists()
            ? null
            : $this->broken('internal', 404, 'not_found', 'Bu hizmete bağlı böyle bir bölge yok.');
    }

    /**
     * Dış adres: önce HEAD denenir (gövde indirilmez), reddedilirse GET'e
     * düşülür — bazı sunucular HEAD'i hiç desteklemez.
     */
    private function checkExternal(string $url, string $host): ?array
    {
        foreach (config('broken-links.skipped_hosts') as $skipped) {
            if ($host === $skipped || str_ends_with($host, ".{$skipped}")) {
                return null;
            }
        }

        $request = Http::withUserAgent(config('broken-links.user_agent'))
            ->withHeaders(['Accept' => '*/*'])
            ->connectTimeout((int) config('broken-links.connect_timeout'))
            ->timeout((int) config('broken-links.timeout'))
            ->withOptions(['allow_redirects' => ['max' => 5, 'strict' => false]]);

        try {
            $response = $request->head($url);

            if ($response->failed()) {
                $response = $request->get($url);
            }
        } catch (ConnectionException $e) {
            return Str::contains($e->getMessage(), ['timed out', 'Timeout', 'timeout'])
                ? $this->broken('external', null, 'timeout', 'Site verilen sürede yanıt vermedi.')
                : $this->broken('external', null, 'connection', 'Sunucuya ulaşılamadı (alan adı ya da sertifika sorunu olabilir).');
        } catch (Throwable) {
            return $this->broken('external', null, 'connection', 'İstek tamamlanamadı.');
        }

        $status = $response->status();

        if ($response->successful() || $response->redirect() || in_array($status, config('broken-links.ignored_statuses'), true)) {
            return null;
        }

        return $status >= 500
            ? $this->broken('external', $status, 'server_error', "Sitenin sunucusu hata döndürdü (HTTP {$status}).")
            : $this->broken('external', $status, 'not_found', "Adres bulunamadı (HTTP {$status}).");
    }

    /** @return array{scope: string, status_code: int|null, reason: string, message: string} */
    private function broken(string $scope, ?int $status, string $reason, string $message): array
    {
        return ['scope' => $scope, 'status_code' => $status, 'reason' => $reason, 'message' => $message];
    }

    private function isOwnHost(string $host): bool
    {
        $appHost = Str::lower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        return $host === $appHost || $host === 'localhost' || Str::startsWith($host, '127.0.0.');
    }
}
