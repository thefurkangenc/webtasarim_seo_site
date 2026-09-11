<?php

namespace App\Listeners;

use App\Services\Health\QueueHealth;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Kuyruk işçisinin "ayaktayım" sinyali. İşçi boşta dönerken de (Looping)
 * tetiklendiği için, iş olmadığı zamanlarda bile sağlık paneli işçinin
 * çalıştığını görebilir.
 *
 * İşçi saniyede birkaç kez döner; her turda cache'e yazmamak için sinyal
 * THROTTLE_SECONDS'ta bir tazelenir.
 */
class RecordQueueHeartbeat
{
    private const THROTTLE_SECONDS = 30;

    public function handle(object $event): void
    {
        try {
            $last = Cache::get(QueueHealth::HEARTBEAT_KEY);

            if ($last && now()->diffInSeconds($last, true) < self::THROTTLE_SECONDS) {
                return;
            }

            Cache::forever(QueueHealth::HEARTBEAT_KEY, now()->toIso8601String());
        } catch (Throwable) {
            // Sinyal yazılamadıysa iş akışı durmaz; panelde "sinyal yok" görünür.
        }
    }
}
