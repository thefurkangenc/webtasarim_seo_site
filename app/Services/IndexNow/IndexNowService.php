<?php

namespace App\Services\IndexNow;

use App\Services\Setting\SettingService;
use App\Services\Sitemap\SitemapService;
use App\Support\Activity;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * IndexNow — içerik değişince arama motorlarına "bu adresi yeniden tara" der.
 * Harici paket yok: protokol tek bir JSON POST'tan ibaret.
 *
 * Doğrulama, sitenin kökünde `{anahtar}.txt` dosyasının anahtarı içermesiyle
 * yapılır; o dosya statik değil, `IndexNowController` tarafından ayardan
 * okunarak sunulur (anahtar değişince elle dosya değiştirmek gerekmez).
 *
 * Google bu protokolü desteklemez — Google tarafı Search Console'dan yürür.
 */
class IndexNowService
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly SitemapService $sitemap,
    ) {}

    /** Panel sayfasının ihtiyaç duyduğu her şey. */
    public function formData(): array
    {
        $key = $this->key();

        return [
            'enabled' => $this->enabled(),
            'autoSubmit' => $this->autoSubmit(),
            'key' => $key,
            'keyUrl' => url("/{$key}.txt"),
            'engines' => config('indexnow.engines'),
            'history' => $this->history(),
            'sitemapUrlCount' => count($this->sitemapUrls()),
        ];
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->get('indexnow', 'enabled', false);
    }

    public function autoSubmit(): bool
    {
        return (bool) $this->settings->get('indexnow', 'auto_submit', true);
    }

    /** Anahtar — yoksa üretilip kaydedilir, böylece panel hiç boş görünmez. */
    public function key(): string
    {
        $key = (string) $this->settings->get('indexnow', 'key', '');

        if ($key === '') {
            $key = $this->generateKey();
            $this->settings->putGroup('indexnow', ['key' => $key]);
        }

        return $key;
    }

    /** @param  array<string, mixed>  $data */
    public function saveSettings(array $data): void
    {
        $this->settings->putGroup('indexnow', [
            'enabled' => filter_var($data['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN) ? '1' : '',
            'auto_submit' => filter_var($data['auto_submit'] ?? false, FILTER_VALIDATE_BOOLEAN) ? '1' : '',
        ]);
    }

    /**
     * Anahtarı yeniler. Eski anahtar anında geçersiz olur; motorlar sonraki
     * bildirimde yeni anahtarı doğrular.
     */
    public function regenerateKey(): string
    {
        $key = $this->generateKey();
        $this->settings->putGroup('indexnow', ['key' => $key]);

        return $key;
    }

    /**
     * Adresleri bildirir. Protokol sınırını aşan liste parçalara bölünür.
     *
     * @param  list<string>  $urls
     * @return array{sent: int, ok: bool, message: string}
     */
    public function submit(array $urls, string $source = 'elle'): array
    {
        if (! $this->enabled()) {
            throw new DomainException('IndexNow kapalı. Önce bu sayfadan açın.');
        }

        $urls = $this->cleanUrls($urls);

        if ($urls === []) {
            throw new DomainException('Bildirilecek geçerli bir adres yok.');
        }

        $key = $this->key();
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $results = [];

        foreach (array_chunk($urls, (int) config('indexnow.batch_size')) as $chunk) {
            $response = Http::acceptJson()->timeout(20)->post(config('indexnow.endpoint'), [
                'host' => $host,
                'key' => $key,
                'keyLocation' => url("/{$key}.txt"),
                'urlList' => $chunk,
            ]);

            $results[] = [
                'status' => $response->status(),
                'ok' => $response->successful(),
                'count' => count($chunk),
            ];
        }

        $ok = collect($results)->every(fn (array $r) => $r['ok']);
        $status = (int) ($results[0]['status'] ?? 0);
        $message = $this->statusMessage($status, count($urls), $ok);

        $this->record($urls, $status, $ok, $message, $source);

        return ['sent' => count($urls), 'ok' => $ok, 'message' => $message];
    }

    /** Site haritasındaki tüm adresleri bildirir. */
    public function submitAll(): array
    {
        $urls = $this->sitemapUrls();

        if ($urls === []) {
            throw new DomainException('Site haritası boş görünüyor. Önce Site Haritası ekranından üretin.');
        }

        return $this->submit($urls, 'tümü');
    }

    /** @return list<array<string, mixed>> En yeni önce. */
    public function history(): array
    {
        return array_map(function (array $entry) {
            $entry['at'] = Carbon::parse($entry['at']);

            return $entry;
        }, Cache::get('indexnow.history', []));
    }

    public function clearHistory(): void
    {
        Cache::forget('indexnow.history');
    }

    /** Kökteki `{anahtar}.txt` isteği — anahtar eşleşirse içeriği, değilse null. */
    public function keyFile(string $key): ?string
    {
        return hash_equals($this->key(), $key) ? $this->key() : null;
    }

    /**
     * Üretilmiş sitemap dosyalarındaki adresler. IndexNow'a gönderilecek "tam
     * liste" için ayrıca sorgu çalıştırmak yerine hazır çıktı okunur.
     *
     * @return list<string>
     */
    private function sitemapUrls(): array
    {
        $index = $this->sitemap->read('sitemap.xml');

        if (! $index) {
            return [];
        }

        $urls = [];

        // İndeks dosyası alt dosyaların adreslerini verir; her birinden <loc>'ları topla.
        foreach ($this->locs($index) as $fileUrl) {
            $content = $this->sitemap->read(basename((string) parse_url($fileUrl, PHP_URL_PATH)));

            if ($content) {
                $urls = array_merge($urls, $this->locs($content));
            }
        }

        return array_values(array_unique($urls));
    }

    /** @return list<string> */
    private function locs(string $xml): array
    {
        preg_match_all('#<loc>(.*?)</loc>#s', $xml, $matches);

        return array_map(fn (string $loc) => html_entity_decode(trim($loc), ENT_QUOTES | ENT_XML1), $matches[1]);
    }

    /**
     * Protokol, bildirilen her adresin ayarlardaki alan adıyla aynı olmasını
     * şart koşuyor; farklı alan adları sessizce 422'ye sebep olurdu.
     *
     * @param  list<string>  $urls
     * @return list<string>
     */
    private function cleanUrls(array $urls): array
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        return collect($urls)
            ->map(fn ($url) => trim((string) $url))
            ->filter(fn (string $url) => $url !== '' && parse_url($url, PHP_URL_HOST) === $host)
            ->unique()
            ->values()
            ->all();
    }

    private function generateKey(): string
    {
        return substr(bin2hex(random_bytes(64)), 0, (int) config('indexnow.key_length'));
    }

    /** @param  list<string>  $urls */
    private function record(array $urls, int $status, bool $ok, string $message, string $source): void
    {
        $history = Cache::get('indexnow.history', []);

        array_unshift($history, [
            // Cache'e Carbon nesnesi değil ISO metni yazılır — süreçler arası
            // (kuyruk işçisi ↔ web isteği) unserialize sorunlarından kaçınmak için.
            'at' => now()->toIso8601String(),
            'count' => count($urls),
            'status' => $status,
            'ok' => $ok,
            'message' => $message,
            'source' => $source,
            'sample' => array_slice($urls, 0, 3),
        ]);

        Cache::forever('indexnow.history', array_slice($history, 0, (int) config('indexnow.history_limit')));

        Activity::record(
            logName: 'indexnow',
            event: $ok ? 'notified' : 'failed',
            description: $message,
            subjectLabel: 'IndexNow',
            properties: ['source' => $source, 'status' => $status, 'urls' => array_slice($urls, 0, 20)],
        );
    }

    private function statusMessage(int $status, int $count, bool $ok): string
    {
        if ($ok) {
            return $status === 202
                ? "{$count} adres bildirildi — anahtar doğrulaması bekleniyor (birkaç dakika sürebilir)."
                : "{$count} adres arama motorlarına bildirildi.";
        }

        return match ($status) {
            400 => 'Bildirim reddedildi: istek biçimi geçersiz.',
            403 => 'Anahtar doğrulanamadı. Sitenin kökündeki anahtar dosyasına erişilebildiğinden emin olun.',
            422 => 'Adresler site alan adıyla uyuşmuyor ya da anahtar adresle eşleşmiyor.',
            429 => 'Çok fazla bildirim gönderildi, bir süre bekleyin.',
            default => "Bildirim başarısız (HTTP {$status}).",
        };
    }
}
