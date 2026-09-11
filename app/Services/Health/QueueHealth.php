<?php

namespace App\Services\Health;

use App\Support\Activity;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Kuyruk sağlığı: işçi ayakta mı, bekleyen iş birikmiş mi, başarısız iş
 * var mı. Yapay zeka üretimi, site haritası ve IndexNow bildirimleri
 * kuyrukta çalıştığı için işçi durduğunda hiçbiri tamamlanmaz.
 */
class QueueHealth
{
    /** İşçinin bıraktığı son sinyal (App\Listeners\RecordQueueHeartbeat). */
    public const HEARTBEAT_KEY = 'health.queue.heartbeat';

    /** @return array<int, Check> */
    public function checks(): array
    {
        return [$this->worker(), $this->failed()];
    }

    /**
     * Başarısız işler — panelde listelenir. Payload'dan iş sınıfı ve
     * exception'ın ilk satırı çıkarılır; tamamı ekranı boğar.
     *
     * @return array<int, array<string, mixed>>
     */
    public function failedJobs(): array
    {
        $rows = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit((int) config('health.queue.failed_list_limit', 25))
            ->get();

        return $rows->map(function (object $row): array {
            $payload = json_decode($row->payload, true);

            return [
                'uuid' => $row->uuid,
                'name' => $payload['displayName'] ?? ($payload['job'] ?? 'Bilinmeyen iş'),
                'queue' => $row->queue,
                'connection' => $row->connection,
                'failed_at' => Carbon::parse($row->failed_at)->toIso8601String(),
                'failed_at_label' => Carbon::parse($row->failed_at)->translatedFormat('d F Y H:i'),
                'exception' => $this->exceptionSummary($row->exception),
            ];
        })->all();
    }

    /** Tek bir başarısız işi kuyruğa geri koyar. */
    public function retry(string $uuid): void
    {
        $this->assertExists($uuid);

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        Activity::record('health', 'job_retry', 'Başarısız iş yeniden kuyruğa alındı',
            properties: ['new' => ['uuid' => $uuid]]);
    }

    /** Tüm başarısız işleri kuyruğa geri koyar; kaç iş olduğunu döner. */
    public function retryAll(): int
    {
        $count = DB::table('failed_jobs')->count();

        if ($count === 0) {
            throw new DomainException('Yeniden denenecek başarısız iş yok.');
        }

        Artisan::call('queue:retry', ['id' => ['all']]);

        Activity::record('health', 'job_retry', $count.' başarısız iş yeniden kuyruğa alındı',
            properties: ['new' => ['count' => $count]]);

        return $count;
    }

    /** Başarısız işi kayıttan siler (yeniden denenmez). */
    public function forget(string $uuid): void
    {
        $this->assertExists($uuid);

        DB::table('failed_jobs')->where('uuid', $uuid)->delete();

        Activity::record('health', 'job_delete', 'Başarısız iş kaydı silindi',
            properties: ['old' => ['uuid' => $uuid]]);
    }

    /** Başarısız iş listesini tamamen temizler; silinen sayıyı döner. */
    public function flush(): int
    {
        $count = DB::table('failed_jobs')->count();

        if ($count === 0) {
            throw new DomainException('Silinecek başarısız iş yok.');
        }

        DB::table('failed_jobs')->delete();

        Activity::record('health', 'job_delete', $count.' başarısız iş kaydı silindi',
            properties: ['old' => ['count' => $count]]);

        return $count;
    }

    private function worker(): Check
    {
        $driver = config('queue.default');

        if ($driver === 'sync') {
            return Check::skipped('queue_worker', 'Kuyruk İşçisi',
                'Kuyruk senkron çalışıyor; işler istek içinde tamamlanıyor.',
                'Canlıda QUEUE_CONNECTION=database olmalı, yoksa yapay zeka üretimi isteği bekletir.');
        }

        $pending = DB::table('jobs')->count();
        $oldest = DB::table('jobs')->min('available_at');
        $waiting = $oldest ? Carbon::createFromTimestamp((int) $oldest)->diffInMinutes(now()) : 0;
        $heartbeat = $this->heartbeat();
        $meta = [
            'pending' => $pending,
            'heartbeat' => $heartbeat?->toIso8601String(),
            'heartbeat_label' => $heartbeat?->diffForHumans(),
        ];

        $hint = 'Sunucuda `php artisan queue:work` çalışıyor olmalı (supervisor ya da benzeri bir süreç yöneticisiyle).';

        if ($heartbeat === null) {
            return Check::critical('queue_worker', 'Kuyruk İşçisi',
                'İşçiden hiç sinyal alınmadı; kuyruk hiç çalıştırılmamış olabilir.', $hint, $meta);
        }

        $age = $heartbeat->diffInMinutes(now());

        if ($age > (int) config('health.queue.heartbeat_minutes', 5)) {
            return Check::critical('queue_worker', 'Kuyruk İşçisi',
                'İşçi '.$heartbeat->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE).
                'dır sinyal vermiyor. '.($pending > 0 ? $pending.' iş sırada bekliyor.' : 'Bekleyen iş yok.'),
                $hint, $meta);
        }

        if ($pending > 0 && $waiting > (int) config('health.queue.pending_age_minutes', 15)) {
            return Check::warning('queue_worker', 'Kuyruk İşçisi',
                'İşçi ayakta ama '.$pending.' iş '.$waiting.' dakikadır sırada bekliyor.',
                'İşler uzun sürüyor ya da tek işçi yetmiyor olabilir.', $meta);
        }

        return Check::ok('queue_worker', 'Kuyruk İşçisi',
            'İşçi çalışıyor'.($pending > 0 ? ', '.$pending.' iş sırada.' : ', kuyruk boş.'), $meta);
    }

    private function failed(): Check
    {
        $count = DB::table('failed_jobs')->count();
        $latest = DB::table('failed_jobs')->max('failed_at');
        $meta = [
            'count' => $count,
            'latest' => $latest ? Carbon::parse($latest)->toIso8601String() : null,
        ];

        if ($count === 0) {
            return Check::ok('failed_jobs', 'Başarısız İşler', 'Başarısız iş yok.', $meta);
        }

        $message = $count.' iş başarısız oldu; son hata '.Carbon::parse($latest)->diffForHumans().'.';
        $hint = 'Hatayı giderdikten sonra aşağıdaki listeden yeniden deneyebilirsin.';

        if ($count >= (int) config('health.queue.failed_critical', 10)) {
            return Check::critical('failed_jobs', 'Başarısız İşler', $message, $hint, $meta);
        }

        if ($count >= (int) config('health.queue.failed_warning', 1)) {
            return Check::warning('failed_jobs', 'Başarısız İşler', $message, $hint, $meta);
        }

        // Eşiğin altında: liste yine gösterilir ama durum sorun sayılmaz.
        return Check::ok('failed_jobs', 'Başarısız İşler', $message, $meta);
    }

    private function heartbeat(): ?Carbon
    {
        try {
            $value = Cache::get(self::HEARTBEAT_KEY);
        } catch (Throwable) {
            return null;
        }

        return $value ? Carbon::parse($value) : null;
    }

    private function assertExists(string $uuid): void
    {
        if (! DB::table('failed_jobs')->where('uuid', $uuid)->exists()) {
            throw new DomainException('Bu başarısız iş kaydı bulunamadı.');
        }
    }

    /** Yığın izinin ilk satırı yeterli; tamamı log ekranında zaten var. */
    private function exceptionSummary(string $exception): string
    {
        $first = strtok($exception, "\n") ?: $exception;

        return mb_strimwidth(trim($first), 0, 220, '…');
    }
}
