<?php

namespace App\Services\Analytics;

use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Service\Service;
use App\Services\Google\GoogleException;
use App\Services\Google\GoogleServiceAccount;
use App\Services\Setting\SettingService;
use App\Support\UrlPath;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * GA4 verisini panele hazırlayan üst katman. Kimlik bilgisi ayarlarda
 * (`analytics` grubu) durur: property_id ve gizli olmayan client_email düz,
 * service account JSON'un tamamı `Crypt` ile şifreli. Raporlar 10 dk,
 * canlı veri 30 sn cache'lenir.
 */
class AnalyticsService
{
    private const RANGES = [7, 28, 90];

    /**
     * Liste ekranlarında satır başına görüntüleme gösterilebilen modeller:
     * istemcinin gönderdiği tür anahtarı => model sınıfı.
     */
    private const VIEWABLE = [
        'page' => Page::class,
        'blog' => Blog::class,
        'service' => Service::class,
    ];

    /*
    | Sayfa bazlı raporda çekilecek en fazla satır. Bölge sayfaları yüzünden
    | adres sayısı büyüyebilir; sıralama görüntülemeye göre olduğu için
    | kesilen kuyrukta zaten sıfıra yakın değerler kalır.
    */
    private const PAGE_VIEW_LIMIT = 5000;

    /** @var array<string, array{0: string, 1: string}> KPI adı => [etiket, biçim] */
    private const KPIS = [
        'activeUsers' => ['Aktif kullanıcı', 'int'],
        'newUsers' => ['Yeni kullanıcı', 'int'],
        'sessions' => ['Oturum', 'int'],
        'screenPageViews' => ['Sayfa görüntüleme', 'int'],
        'averageSessionDuration' => ['Ort. oturum süresi', 'duration'],
        'bounceRate' => ['Hemen çıkma oranı', 'rate'],
    ];

    public function __construct(private readonly SettingService $settings) {}

    public function configured(): bool
    {
        return filled($this->settings->get('analytics', 'property_id'))
            && filled($this->settings->get('analytics', 'service_account'));
    }

    public function propertyId(): ?string
    {
        return $this->settings->get('analytics', 'property_id') ?: null;
    }

    public function clientEmail(): ?string
    {
        return $this->settings->get('analytics', 'client_email') ?: null;
    }

    /**
     * @param  array{property_id: string, service_account: string|null}  $data
     */
    public function saveSettings(array $data): void
    {
        $payload = ['property_id' => preg_replace('/\D+/', '', (string) $data['property_id'])];
        $newKey = filled($data['service_account'] ?? null);

        if ($newKey) {
            $payload['client_email'] = GoogleServiceAccount::fromJson((string) $data['service_account'])->clientEmail;
            $payload['service_account'] = Crypt::encryptString((string) $data['service_account']);
        }

        $this->settings->putGroup('analytics', $payload);
        $this->flush();
    }

    /** Bağlantı testi — küçük bir rapor çeker. @return array{ok: bool, message: string} */
    public function test(): array
    {
        $client = $this->client();

        if (! $client) {
            return ['ok' => false, 'message' => 'Önce property ID ve service account JSON girin.'];
        }

        try {
            $report = $client->runReport([
                'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
                'metrics' => [['name' => 'activeUsers']],
            ]);

            $users = $report['rows'][0]['metricValues'][0]['value'] ?? '0';
            $this->flush();

            return ['ok' => true, 'message' => "Bağlantı başarılı — son 7 günde {$users} aktif kullanıcı."];
        } catch (GoogleException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Panelin ana verisi.
     *
     * @return array<string, mixed>
     */
    public function summary(int $days): array
    {
        $days = in_array($days, self::RANGES, true) ? $days : 28;

        return Cache::remember("analytics.summary.{$days}", now()->addMinutes(10), function () use ($days) {
            $client = $this->requireClient();

            $metrics = array_map(fn ($name) => ['name' => $name], array_keys(self::KPIS));
            $current = ['startDate' => ($days - 1).'daysAgo', 'endDate' => 'today'];
            $previous = ['startDate' => (2 * $days - 1).'daysAgo', 'endDate' => $days.'daysAgo'];

            [$kpiNow, $kpiPrev, $series, $pages, $channels] = $client->batchRunReports([
                ['dateRanges' => [$current], 'metrics' => $metrics],
                ['dateRanges' => [$previous], 'metrics' => $metrics],
                [
                    'dateRanges' => [$current],
                    'dimensions' => [['name' => 'date']],
                    'metrics' => [['name' => 'sessions'], ['name' => 'activeUsers']],
                    'orderBys' => [['dimension' => ['dimensionName' => 'date']]],
                ],
                [
                    'dateRanges' => [$current],
                    'dimensions' => [['name' => 'pagePath'], ['name' => 'pageTitle']],
                    'metrics' => [['name' => 'screenPageViews']],
                    'orderBys' => [['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]],
                    'limit' => 10,
                ],
                [
                    'dateRanges' => [$current],
                    'dimensions' => [['name' => 'sessionDefaultChannelGroup']],
                    'metrics' => [['name' => 'sessions']],
                    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
                    'limit' => 8,
                ],
            ]);

            [$devices, $countries] = $client->batchRunReports([
                [
                    'dateRanges' => [$current],
                    'dimensions' => [['name' => 'deviceCategory']],
                    'metrics' => [['name' => 'sessions']],
                    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
                ],
                [
                    'dateRanges' => [$current],
                    'dimensions' => [['name' => 'country']],
                    'metrics' => [['name' => 'activeUsers']],
                    'orderBys' => [['metric' => ['metricName' => 'activeUsers'], 'desc' => true]],
                    'limit' => 8,
                ],
            ]);

            return [
                'range_days' => $days,
                'updated_at' => now()->toIso8601String(),
                'kpis' => $this->kpis($this->metricRow($kpiNow), $this->metricRow($kpiPrev)),
                'timeseries' => $this->timeseries($series),
                'top_pages' => array_map(fn ($r) => [
                    'path' => $r['dims'][0] ?? '/',
                    'title' => $r['dims'][1] ?? '',
                    'views' => (int) ($r['metrics'][0] ?? 0),
                ], $this->dimensionRows($pages)),
                'channels' => $this->named($channels),
                'devices' => $this->named($devices),
                'countries' => array_map(fn ($r) => [
                    'name' => ($r['dims'][0] ?? '') ?: '—',
                    'users' => (int) ($r['metrics'][0] ?? 0),
                ], $this->dimensionRows($countries)),
            ];
        });
    }

    /** @return array<string, mixed> */
    public function realtime(): array
    {
        return Cache::remember('analytics.realtime', now()->addSeconds(30), function () {
            $client = $this->requireClient();

            $total = $client->runRealtimeReport(['metrics' => [['name' => 'activeUsers']]]);
            $byPage = $client->runRealtimeReport([
                'dimensions' => [['name' => 'unifiedScreenName']],
                'metrics' => [['name' => 'activeUsers']],
                'orderBys' => [['metric' => ['metricName' => 'activeUsers'], 'desc' => true]],
                'limit' => 8,
            ]);

            return [
                'active_users' => (int) ($total['rows'][0]['metricValues'][0]['value'] ?? 0),
                'pages' => array_map(fn ($r) => [
                    'name' => $r['dims'][0] ?? '—',
                    'users' => (int) ($r['metrics'][0] ?? 0),
                ], $this->dimensionRows($byPage)),
                'updated_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Belirtilen kayıtların son N gündeki sayfa görüntülemeleri.
     *
     * Liste ekranlarının yanında gösterilir ve o ekranlar GA4'e bağımlı
     * olmamalıdır: bağlantı yoksa ya da rapor alınamazsa istisna fırlatılmaz,
     * `available: false` döner ve arayüz kolonu gizler.
     *
     * @param  list<int>  $ids
     * @return array{available: bool, days: int, views: array<int, int>}
     */
    public function viewsFor(string $type, array $ids, int $days = 28): array
    {
        $days = in_array($days, self::RANGES, true) ? $days : 28;
        $model = self::VIEWABLE[$type] ?? null;
        $views = $model ? $this->pageViews($days) : null;

        // Boş harita geçerli bir sonuçtur (hiç trafik yok); yalnızca null
        // "rapor alınamadı" demektir — ikisi karıştırılırsa sessiz bir dönemde
        // kolon kendini boş yere kapatır.
        if ($views === null) {
            return ['available' => false, 'days' => $days, 'views' => []];
        }

        $result = [];

        foreach ($model::whereKey($ids)->get() as $record) {
            // Yayında olmayan kaydın da adresi çözülür (indexNowUrl yayın
            // durumuna bakmaz): yayından yeni kaldırılmış bir içeriğin geçmiş
            // trafiği görünmeye devam etmeli.
            $path = $record->indexNowUrl();

            // Adresi olmayan kayıt (slug'ı boş taslak) ana sayfanın sayısını
            // devralmasın: normalize("") ile ana sayfa aynı anahtara düşüyor.
            $result[$record->id] = $path ? ($views[UrlPath::normalize($path)] ?? 0) : 0;
        }

        return ['available' => true, 'days' => $days, 'views' => $result];
    }

    /**
     * Adres => görüntüleme haritası. Tek bir GA4 raporuyla tüm site çekilir,
     * 30 dk cache'lenir; liste ekranı kaç satır gösterirse göstersin Google'a
     * giden istek sayısı değişmez.
     *
     * @return array<string, int>|null Rapor alınamadıysa null.
     */
    private function pageViews(int $days): ?array
    {
        $key = "analytics.page_views.{$days}";

        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }

        if (! $client = $this->client()) {
            return null;
        }

        try {
            $report = $client->runReport([
                'dateRanges' => [['startDate' => ($days - 1).'daysAgo', 'endDate' => 'today']],
                'dimensions' => [['name' => 'pagePath']],
                'metrics' => [['name' => 'screenPageViews']],
                'orderBys' => [['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]],
                'limit' => self::PAGE_VIEW_LIMIT,
            ]);
        } catch (GoogleException|ConnectionException) {
            // Yetki/kota hatası da, Google'a hiç ulaşılamaması da liste ekranını
            // bozmamalı. Başarısız sonuç cache'lenmez: bağlantı düzelince ilk
            // istekte sayılar geri gelir.
            return null;
        }

        $views = [];

        foreach ($this->dimensionRows($report) as $row) {
            // GA4 aynı sayfayı sorgu dizesiyle ayrı satırlarda verir
            // (?utm_source=...); normalleştirip toplamak gerekiyor.
            $path = UrlPath::normalize($row['dims'][0] ?? '');
            $views[$path] = ($views[$path] ?? 0) + (int) ($row['metrics'][0] ?? 0);
        }

        Cache::put($key, $views, now()->addMinutes(30));

        return $views;
    }

    public function client(): ?GoogleAnalyticsClient
    {
        $account = $this->serviceAccount();
        $propertyId = $this->settings->get('analytics', 'property_id');

        if (! $account || blank($propertyId)) {
            return null;
        }

        return new GoogleAnalyticsClient($account, (string) $propertyId);
    }

    /**
     * Kayıtlı service account kimliği — Search Console gibi diğer Google
     * servisleri de aynı JSON'u kullanır, kullanıcı ikinci kez girmez.
     */
    public function serviceAccount(): ?GoogleServiceAccount
    {
        $encrypted = $this->settings->get('analytics', 'service_account');

        if (blank($encrypted)) {
            return null;
        }

        try {
            return GoogleServiceAccount::fromJson(Crypt::decryptString($encrypted));
        } catch (\Throwable) {
            return null;
        }
    }

    private function requireClient(): GoogleAnalyticsClient
    {
        return $this->client() ?? throw new AnalyticsException('GA4 bağlantısı yapılandırılmamış.');
    }

    public function flush(): void
    {
        Cache::forget('analytics.realtime');

        foreach (self::RANGES as $days) {
            Cache::forget("analytics.summary.{$days}");
            Cache::forget("analytics.page_views.{$days}");
        }
    }

    /**
     * @param  array<string, float>  $current
     * @param  array<string, float>  $previous
     * @return list<array<string, mixed>>
     */
    private function kpis(array $current, array $previous): array
    {
        $kpis = [];

        foreach (self::KPIS as $name => [$label, $format]) {
            $now = $current[$name] ?? 0.0;
            $prev = $previous[$name] ?? 0.0;

            $kpis[] = [
                'key' => $name,
                'label' => $label,
                'format' => $format,
                'value' => $now,
                'previous' => $prev,
                'change' => $prev > 0 ? round((($now - $prev) / $prev) * 100, 1) : null,
                'lower_is_better' => $name === 'bounceRate',
            ];
        }

        return $kpis;
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array{labels: list<string>, sessions: list<int>, users: list<int>}
     */
    private function timeseries(array $report): array
    {
        $out = ['labels' => [], 'sessions' => [], 'users' => []];

        foreach ($this->dimensionRows($report) as $row) {
            $raw = $row['dims'][0] ?? '';
            $date = Carbon::createFromFormat('Ymd', $raw) ?: null;
            $out['labels'][] = $date ? $date->locale('tr')->isoFormat('D MMM') : $raw;
            $out['sessions'][] = (int) ($row['metrics'][0] ?? 0);
            $out['users'][] = (int) ($row['metrics'][1] ?? 0);
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $report
     * @return list<array{name: string, sessions: int}>
     */
    private function named(array $report): array
    {
        return array_map(fn ($r) => [
            'name' => ($r['dims'][0] ?? '') ?: '—',
            'sessions' => (int) ($r['metrics'][0] ?? 0),
        ], $this->dimensionRows($report));
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, float>
     */
    private function metricRow(array $report): array
    {
        $headers = array_map(fn ($h) => $h['name'] ?? '', $report['metricHeaders'] ?? []);
        $values = $report['rows'][0]['metricValues'] ?? [];
        $out = [];

        foreach ($headers as $i => $name) {
            $out[$name] = (float) ($values[$i]['value'] ?? 0);
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $report
     * @return list<array{dims: list<string>, metrics: list<float>}>
     */
    private function dimensionRows(array $report): array
    {
        $rows = [];

        foreach ($report['rows'] ?? [] as $row) {
            $rows[] = [
                'dims' => array_map(fn ($d) => (string) ($d['value'] ?? ''), $row['dimensionValues'] ?? []),
                'metrics' => array_map(fn ($m) => (float) ($m['value'] ?? 0), $row['metricValues'] ?? []),
            ];
        }

        return $rows;
    }
}
