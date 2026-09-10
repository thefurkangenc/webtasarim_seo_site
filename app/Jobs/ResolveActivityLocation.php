<?php

namespace App\Jobs;

use App\Models\ActivityLog\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Log kaydının IP adresini ülke/şehir bilgisine çevirir.
 *
 * Kuyrukta çalışır: konum servisi yavaşlasa ya da çökse bile asıl istek
 * etkilenmez, log kaydı zaten eksiksiz yazılmıştır — burada yalnızca boş
 * coğrafya kolonları doldurulur.
 *
 * Aynı IP tekrar tekrar sorulmaz; sonuç önbelleğe alınır. Ücretsiz uç
 * noktanın dakikada ~45 istek sınırı var, önbellek bunu pratikte hiç
 * zorlamamamızı sağlıyor.
 *
 * ÖNEMLİ: `php artisan queue:work` çalışmıyorsa konumlar "pending"de kalır;
 * logun geri kalanı yine de eksiksizdir.
 */
class ResolveActivityLocation implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $backoff = 30;

    public function __construct(
        private readonly int $logId,
        private readonly string $ip,
    ) {}

    public function handle(): void
    {
        $log = ActivityLog::find($this->logId);

        if (! $log) {
            return; // Log arada silinmiş (temizlik komutu vb.)
        }

        $location = $this->lookup();

        if ($location === null) {
            $log->update(['geo_status' => 'failed']);

            return;
        }

        $log->update([...$location, 'geo_status' => 'done']);
    }

    /**
     * @return array<string, mixed>|null Çözümlenemezse null
     */
    private function lookup(): ?array
    {
        $key = 'activity-geo:'.$this->ip;
        $days = (int) config('activity-log.geo.cache_days', 30);

        // Cache::remember null'ı saklamaz; başarısızlığı da önbelleğe almak
        // için sonuç bir diziye sarılıyor (['ok' => false] gibi).
        $cached = Cache::remember($key, now()->addDays($days), function () {
            return ['data' => $this->fetch()];
        });

        return $cached['data'];
    }

    /** @return array<string, mixed>|null */
    private function fetch(): ?array
    {
        $endpoint = str_replace('{ip}', urlencode($this->ip), (string) config('activity-log.geo.endpoint'));

        try {
            $response = Http::timeout((int) config('activity-log.geo.timeout', 4))->get($endpoint);
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        if (! $response->successful() || $response->json('status') !== 'success') {
            return null;
        }

        return [
            'country_code' => $response->json('countryCode'),
            'country' => $response->json('country'),
            'region' => $response->json('regionName'),
            'city' => $response->json('city'),
            'timezone' => $response->json('timezone'),
            'isp' => $response->json('isp'),
            'latitude' => $response->json('lat'),
            'longitude' => $response->json('lon'),
        ];
    }
}
