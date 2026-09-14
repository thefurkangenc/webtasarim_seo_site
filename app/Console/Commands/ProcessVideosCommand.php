<?php

namespace App\Console\Commands;

use App\Jobs\ProcessVideoJob;
use App\Models\Media\Media;
use Illuminate\Console\Command;

/**
 * Mevcut kütüphane videolarını kalite kopyaları için kuyruğa alır.
 * Yeni yüklemeler zaten MediaService içinde işe basılır; bu komut backfill
 * ve takılı/failed kayıtlar içindir. Cron'a bağlı değildir.
 *
 *   php artisan video:process
 */
class ProcessVideosCommand extends Command
{
    protected $signature = 'video:process';

    protected $description = 'Kütüphanedeki videoları kalite kopyaları için kuyruğa alır.';

    public function handle(): int
    {
        $stale = now()->subMinutes(15);
        $queued = 0;

        Media::query()
            ->whereIn('extension', ['mp4', 'webm'])
            ->where(function ($query) use ($stale) {
                $query->whereNull('video')
                    ->orWhere('video->status', 'failed')
                    ->orWhere(function ($query) use ($stale) {
                        $query->where('video->status', 'processing')
                            ->where('updated_at', '<', $stale);
                    });
            })
            ->each(function (Media $media) use (&$queued) {
                $media->video = ['status' => 'processing', 'error' => null];
                $media->saveQuietly();
                ProcessVideoJob::dispatch($media->id);
                $queued++;
            });

        $this->info($queued === 0
            ? 'Kuyruğa alınacak video yok.'
            : "{$queued} video kuyruğa alındı.");

        return self::SUCCESS;
    }
}
