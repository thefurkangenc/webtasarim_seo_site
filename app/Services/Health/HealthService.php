<?php

namespace App\Services\Health;

use App\Contracts\ProvidesMenuBadge;
use App\Mail\Health\HealthAlert;
use App\Support\Activity;
use App\Support\Settings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sağlık kontrollerini toplar, sonucu cache'ler ve panele/sidebar rozetine
 * verir. Kontroller ağ ve disk erişimi yaptığı için HER İSTEKTE ÇALIŞMAZ:
 * rapor `health.report` anahtarında durur, saatlik `health:check` komutu
 * ve panelin "Yeniden Tara" butonu tazeler.
 */
class HealthService implements ProvidesMenuBadge
{
    public const CACHE_KEY = 'health.report';

    /** Günde bir bildirim: en son hangi güne mail atıldığı. */
    private const NOTIFIED_KEY = 'health.notified_on';

    public function __construct(
        private readonly QueueHealth $queue,
        private readonly SystemHealth $system,
        private readonly ConnectivityHealth $connectivity,
    ) {}

    /**
     * Cache'teki rapor; bayatsa ya da $fresh ise kontroller yeniden koşar.
     *
     * @return array<string, mixed>
     */
    public function report(bool $fresh = false): array
    {
        $cached = $this->cached();

        if (! $fresh && $cached !== null && ! $this->stale($cached)) {
            return $cached;
        }

        return $this->run();
    }

    /** @return array<string, mixed>|null */
    public function cached(): ?array
    {
        try {
            return Cache::get(self::CACHE_KEY);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Panelin ilk yüklemesi ve AJAX yenilemesi için tek gövde.
     *
     * @return array<string, mixed>
     */
    public function indexData(bool $fresh = false): array
    {
        $report = $this->report($fresh);

        return [
            ...$report,
            'checked_ago' => Carbon::parse($report['checked_at'])->diffForHumans(),
            'failed_jobs' => $this->queue->failedJobs(),
        ];
    }

    /**
     * Sidebar rozeti ve dashboard kartı için yalnızca CACHE'ten okur —
     * rozet uğruna her sayfa yüklemesinde SMTP'ye bağlanılmaz.
     *
     * @return array<string, mixed>|null
     */
    public function menuBadge(): ?array
    {
        $report = $this->cached();

        if ($report === null || $report['status'] === 'ok') {
            return null;
        }

        $count = $report['counts']['critical'] + $report['counts']['warning'];

        return ['count' => $count, 'status' => $report['status']];
    }

    /**
     * Kritik sorun varsa günde bir kez yöneticiye özet e-posta.
     * Gönderim kuyruğa alınmaz: kuyruk zaten çalışmıyor olabilir.
     */
    public function notify(bool $force = false): bool
    {
        $report = $this->report();

        if ($report['counts']['critical'] === 0) {
            return false;
        }

        if (! $force && Cache::get(self::NOTIFIED_KEY) === now()->toDateString()) {
            return false;
        }

        $to = filled(Settings::get('contact.to_email'))
            ? Settings::get('contact.to_email')
            : Settings::get('company.email');

        if (blank($to)) {
            return false;
        }

        Mail::to($to)->send(new HealthAlert($report));

        Cache::put(self::NOTIFIED_KEY, now()->toDateString(), now()->addDays(2));

        Activity::record('health', 'alert', $report['counts']['critical'].' kritik sorun için uyarı e-postası gönderildi',
            properties: ['new' => ['to' => $to, 'checks' => $this->failingKeys($report)]],
            severity: 'warning');

        return true;
    }

    /** @return array<string, mixed> */
    private function run(): array
    {
        $checks = [
            ...$this->queue->checks(),
            ...$this->system->checks(),
            ...$this->connectivity->checks(),
        ];

        $checks = array_map(fn (Check $check) => $check->toArray(), $checks);
        $counts = ['critical' => 0, 'warning' => 0, 'ok' => 0, 'skipped' => 0];

        foreach ($checks as $check) {
            $counts[$check['status']]++;
        }

        // Sorunlu kartlar üstte; aynı durumdakiler kontrol sırasını korur.
        usort($checks, fn (array $a, array $b) => $a['order'] <=> $b['order']);

        $report = [
            'checks' => $checks,
            'counts' => $counts,
            'status' => match (true) {
                $counts['critical'] > 0 => 'critical',
                $counts['warning'] > 0 => 'warning',
                default => 'ok',
            },
            'checked_at' => now()->toIso8601String(),
        ];

        Cache::forever(self::CACHE_KEY, $report);

        return $report;
    }

    /** @param array<string, mixed> $report */
    private function stale(array $report): bool
    {
        return Carbon::parse($report['checked_at'])->diffInMinutes(now())
            >= (int) config('health.cache_minutes', 15);
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<int, string>
     */
    private function failingKeys(array $report): array
    {
        return array_column(
            array_filter($report['checks'], fn (array $check) => $check['status'] === 'critical'),
            'key'
        );
    }
}
