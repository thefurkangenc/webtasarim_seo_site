<?php

namespace App\Services\Analytics;

use App\Services\Setting\SettingService;
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
            $parsed = $this->parseServiceAccount((string) $data['service_account']);
            $payload['client_email'] = $parsed['client_email'];
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
        } catch (AnalyticsException $e) {
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

    public function client(): ?GoogleAnalyticsClient
    {
        $encrypted = $this->settings->get('analytics', 'service_account');
        $propertyId = $this->settings->get('analytics', 'property_id');

        if (blank($encrypted) || blank($propertyId)) {
            return null;
        }

        try {
            $sa = $this->parseServiceAccount(Crypt::decryptString($encrypted));
        } catch (\Throwable) {
            return null;
        }

        return new GoogleAnalyticsClient($sa['client_email'], $sa['private_key'], $sa['token_uri'], (string) $propertyId);
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
        }
    }

    /**
     * @return array{client_email: string, private_key: string, token_uri: string}
     */
    private function parseServiceAccount(string $json): array
    {
        $data = json_decode($json, true);

        if (! is_array($data) || blank($data['client_email'] ?? null) || blank($data['private_key'] ?? null)) {
            throw new AnalyticsException('Geçerli bir service account JSON dosyası değil (client_email / private_key eksik).');
        }

        return [
            'client_email' => (string) $data['client_email'],
            'private_key' => (string) $data['private_key'],
            'token_uri' => (string) ($data['token_uri'] ?? 'https://oauth2.googleapis.com/token'),
        ];
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
