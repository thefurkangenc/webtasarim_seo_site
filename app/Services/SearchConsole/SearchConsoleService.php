<?php

namespace App\Services\SearchConsole;

use App\Services\Analytics\AnalyticsService;
use App\Services\Google\GoogleException;
use App\Services\Setting\SettingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Search Console verisini panele hazırlayan üst katman.
 *
 * Kimlik: GA4 ile AYNI service account JSON'u (`analytics` grubu, şifreli) —
 * kullanıcı ikinci bir dosya girmez. Buraya özel tek ayar, hangi Search Console
 * mülkünün okunacağı: `search_console` grubu > `site_url`
 * (`https://siteniz.com/` ya da `sc-domain:siteniz.com`).
 *
 * Raporlar config('search-console.cache_minutes') kadar cache'lenir; Google
 * veriyi günlük güncellediği için sık istek atmanın faydası yok.
 */
class SearchConsoleService
{
    /** @var array<string, array{0: string, 1: string, 2: bool}> metrik => [etiket, biçim, az olması iyi mi] */
    private const KPIS = [
        'clicks' => ['Tıklama', 'int', false],
        'impressions' => ['Gösterim', 'int', false],
        'ctr' => ['Tıklama oranı', 'rate', false],
        'position' => ['Ortalama sıra', 'decimal', true],
    ];

    public function __construct(
        private readonly SettingService $settings,
        private readonly AnalyticsService $analytics,
    ) {}

    /** Panel sayfasının ihtiyaç duyduğu her şey; raporlar AJAX ile gelir. */
    public function formData(): array
    {
        return [
            'siteUrl' => $this->siteUrl(),
            'credentialsReady' => $this->analytics->serviceAccount() !== null,
            'configured' => $this->configured(),
            'clientEmail' => $this->analytics->clientEmail(),
            'sitemapUrl' => route('sitemap.index'),
            'verificationReady' => filled($this->settings->get('tracking', 'google_site_verification')),
            'ranges' => config('search-console.ranges'),
            'lagDays' => config('search-console.lag_days'),
        ];
    }

    public function configured(): bool
    {
        return filled($this->siteUrl()) && $this->analytics->serviceAccount() !== null;
    }

    public function siteUrl(): ?string
    {
        return $this->settings->get('search_console', 'site_url') ?: null;
    }

    /** @param  array{site_url: string|null}  $data */
    public function saveSettings(array $data): void
    {
        $this->settings->putGroup('search_console', [
            'site_url' => trim((string) ($data['site_url'] ?? '')),
        ]);

        $this->flush();
    }

    /**
     * Service account'un erişebildiği mülkler — panelde açılır listeyi doldurur.
     * Site adresi henüz seçilmemişken de çalışır.
     *
     * @return list<array{site_url: string, permission: string}>
     */
    public function sites(): array
    {
        return $this->clientFor('')->sites();
    }

    /** Bağlantı testi. @return array{ok: bool, message: string} */
    public function test(): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'message' => 'Önce Analitik ayarlarından service account JSON girin, sonra bir site adresi seçin.'];
        }

        try {
            $rows = $this->requireClient()->searchAnalytics([
                'startDate' => $this->end()->copy()->subDays(27)->toDateString(),
                'endDate' => $this->end()->toDateString(),
                'rowLimit' => 1,
            ]);

            $clicks = $rows[0]['clicks'] ?? 0;
            $this->flush();

            return ['ok' => true, 'message' => "Bağlantı başarılı — son 28 günde {$clicks} tıklama."];
        } catch (GoogleException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Panelin ana verisi: KPI'lar + eğilim + sorgu/sayfa/ülke/cihaz kırılımı.
     *
     * @return array<string, mixed>
     */
    public function performance(int $days): array
    {
        $ranges = config('search-console.ranges');
        $days = in_array($days, $ranges, true) ? $days : 28;

        return Cache::remember("search-console.performance.{$days}", now()->addMinutes((int) config('search-console.cache_minutes')), function () use ($days) {
            $client = $this->requireClient();
            $limits = config('search-console.row_limits');

            $end = $this->end();
            $start = $end->copy()->subDays($days - 1);
            $prevEnd = $start->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($days - 1);

            $window = ['startDate' => $start->toDateString(), 'endDate' => $end->toDateString()];

            $totals = $client->searchAnalytics($window);
            $previous = $client->searchAnalytics(['startDate' => $prevStart->toDateString(), 'endDate' => $prevEnd->toDateString()]);
            $series = $client->searchAnalytics($window + ['dimensions' => ['date'], 'rowLimit' => 500]);
            $queries = $client->searchAnalytics($window + ['dimensions' => ['query'], 'rowLimit' => $limits['queries']]);
            $pages = $client->searchAnalytics($window + ['dimensions' => ['page'], 'rowLimit' => $limits['pages']]);
            $countries = $client->searchAnalytics($window + ['dimensions' => ['country'], 'rowLimit' => $limits['countries']]);
            $devices = $client->searchAnalytics($window + ['dimensions' => ['device'], 'rowLimit' => 5]);

            return [
                'range_days' => $days,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'updated_at' => now()->toIso8601String(),
                'kpis' => $this->kpis($totals[0] ?? null, $previous[0] ?? null),
                'timeseries' => $this->timeseries($series),
                'queries' => array_map(fn (array $row) => $this->row($row, 'query'), $queries),
                'pages' => array_map(fn (array $row) => $this->row($row, 'page'), $pages),
                'countries' => array_map(fn (array $row) => [
                    'name' => $this->label('country', strtolower($row['keys'][0] ?? ''), strtoupper($row['keys'][0] ?? '—')),
                    'clicks' => $row['clicks'],
                    'impressions' => $row['impressions'],
                ], $countries),
                'devices' => array_map(fn (array $row) => [
                    'name' => $this->label('device', $row['keys'][0] ?? '', $row['keys'][0] ?? '—'),
                    'clicks' => $row['clicks'],
                ], $devices),
            ];
        });
    }

    /**
     * Search Console'a bildirilmiş site haritaları + bizim sitemap.xml'in
     * gönderilmiş olup olmadığı.
     *
     * @return array<string, mixed>
     */
    public function sitemaps(): array
    {
        $ours = route('sitemap.index');
        $list = array_map(fn (array $entry) => [
            'path' => (string) ($entry['path'] ?? ''),
            'is_index' => (bool) ($entry['isSitemapsIndex'] ?? false),
            'is_pending' => (bool) ($entry['isPending'] ?? false),
            'last_submitted' => $this->date($entry['lastSubmitted'] ?? null),
            'last_downloaded' => $this->date($entry['lastDownloaded'] ?? null),
            'warnings' => (int) ($entry['warnings'] ?? 0),
            'errors' => (int) ($entry['errors'] ?? 0),
            'submitted_urls' => (int) array_sum(array_column($entry['contents'] ?? [], 'submitted')),
        ], $this->requireClient()->sitemaps());

        return [
            'our_url' => $ours,
            'submitted' => collect($list)->contains(fn (array $entry) => $entry['path'] === $ours),
            'list' => $list,
        ];
    }

    /** Bizim sitemap.xml'i Search Console'a bildirir. */
    public function submitSitemap(): void
    {
        $this->requireClient()->submitSitemap(route('sitemap.index'));
    }

    /**
     * Tek bir adresin Google'daki durumu (indekslendi mi, ne zaman tarandı).
     * Günlük kota sınırlı olduğu için sonuç 1 saat cache'lenir.
     *
     * @return array<string, mixed>
     */
    public function inspect(string $url): array
    {
        return Cache::remember('search-console.inspect.'.sha1($url), now()->addHour(), function () use ($url) {
            $result = $this->requireClient()->inspect($url);
            $index = $result['indexStatusResult'] ?? [];
            $mobile = $result['mobileUsabilityResult'] ?? [];

            return [
                'url' => $url,
                'link' => $result['inspectionResultLink'] ?? null,
                'verdict' => $index['verdict'] ?? 'VERDICT_UNSPECIFIED',
                'verdict_label' => $this->label('verdict', $index['verdict'] ?? '', 'Bilgi yok'),
                'coverage' => $this->label('coverage', $index['coverageState'] ?? '', $index['coverageState'] ?? '—'),
                'robots' => $this->label('robots', $index['robotsTxtState'] ?? '', 'Bilgi yok'),
                'indexing' => $this->label('indexing', $index['indexingState'] ?? '', 'Bilgi yok'),
                'fetch' => $this->label('fetch', $index['pageFetchState'] ?? '', 'Bilgi yok'),
                'last_crawl' => $this->date($index['lastCrawlTime'] ?? null),
                'google_canonical' => $index['googleCanonical'] ?? null,
                'user_canonical' => $index['userCanonical'] ?? null,
                'sitemaps' => array_map('strval', $index['sitemap'] ?? []),
                'referring_urls' => array_map('strval', $index['referringUrls'] ?? []),
                'mobile_verdict' => $this->label('verdict', $mobile['verdict'] ?? '', 'Bilgi yok'),
                'mobile_issues' => array_map(
                    fn (array $issue) => (string) ($issue['message'] ?? $issue['issueType'] ?? ''),
                    $mobile['issues'] ?? [],
                ),
                'rich_results' => $this->label('verdict', $result['richResultsResult']['verdict'] ?? '', 'Bilgi yok'),
            ];
        });
    }

    public function flush(): void
    {
        foreach (config('search-console.ranges') as $days) {
            Cache::forget("search-console.performance.{$days}");
        }
    }

    /** Raporların bitiş günü — Google'ın veri gecikmesi kadar geriye çekilir. */
    private function end(): Carbon
    {
        return now()->subDays((int) config('search-console.lag_days'))->startOfDay();
    }

    private function requireClient(): SearchConsoleClient
    {
        $siteUrl = $this->siteUrl();

        if (blank($siteUrl)) {
            throw new SearchConsoleException('Önce bir Search Console site adresi seçin.');
        }

        return $this->clientFor($siteUrl);
    }

    private function clientFor(string $siteUrl): SearchConsoleClient
    {
        $account = $this->analytics->serviceAccount()
            ?? throw new SearchConsoleException('Service account JSON bulunamadı. Ayarlar → Analitik bölümünden yükleyin.');

        return new SearchConsoleClient($account, $siteUrl);
    }

    /**
     * @param  array{clicks: int, impressions: int, ctr: float, position: float}|null  $current
     * @param  array{clicks: int, impressions: int, ctr: float, position: float}|null  $previous
     * @return list<array<string, mixed>>
     */
    private function kpis(?array $current, ?array $previous): array
    {
        $kpis = [];

        foreach (self::KPIS as $key => [$label, $format, $lowerIsBetter]) {
            $now = (float) ($current[$key] ?? 0);
            $prev = (float) ($previous[$key] ?? 0);

            $kpis[] = [
                'key' => $key,
                'label' => $label,
                'format' => $format,
                'value' => $now,
                'previous' => $prev,
                'change' => $prev > 0 ? round((($now - $prev) / $prev) * 100, 1) : null,
                'lower_is_better' => $lowerIsBetter,
            ];
        }

        return $kpis;
    }

    /**
     * @param  list<array{keys: list<string>, clicks: int, impressions: int}>  $rows
     * @return array{labels: list<string>, clicks: list<int>, impressions: list<int>}
     */
    private function timeseries(array $rows): array
    {
        $out = ['labels' => [], 'clicks' => [], 'impressions' => []];

        // Google tarihe göre sıralı döndürmeyi garanti etmiyor.
        usort($rows, fn (array $a, array $b) => ($a['keys'][0] ?? '') <=> ($b['keys'][0] ?? ''));

        foreach ($rows as $row) {
            $raw = $row['keys'][0] ?? '';
            $out['labels'][] = ($date = rescue(fn () => Carbon::parse($raw), null, false))
                ? $date->locale('tr')->isoFormat('D MMM')
                : $raw;
            $out['clicks'][] = $row['clicks'];
            $out['impressions'][] = $row['impressions'];
        }

        return $out;
    }

    /**
     * @param  array{keys: list<string>, clicks: int, impressions: int, ctr: float, position: float}  $row
     * @return array<string, mixed>
     */
    private function row(array $row, string $dimension): array
    {
        $value = $row['keys'][0] ?? '';

        return [
            $dimension => $value,
            'label' => $dimension === 'page' ? (parse_url($value, PHP_URL_PATH) ?: $value) : $value,
            'clicks' => $row['clicks'],
            'impressions' => $row['impressions'],
            'ctr' => $row['ctr'],
            'position' => round($row['position'], 1),
        ];
    }

    private function label(string $set, string $key, string $fallback): string
    {
        return config("search-console.labels.{$set}.{$key}") ?? ($key !== '' ? $fallback : 'Bilgi yok');
    }

    private function date(mixed $value): ?string
    {
        return filled($value) ? rescue(fn () => Carbon::parse($value)->toIso8601String(), null, false) : null;
    }
}
