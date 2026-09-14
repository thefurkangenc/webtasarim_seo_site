<?php

namespace App\Jobs;

use App\Models\Media\Media;
use App\Services\Media\VideoProcessor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Kütüphane videosundan kalite kopyaları, poster ve hover sprite üretir.
 * Aynı dosya iki kez kuyruğa girmez (`ShouldBeUnique`).
 */
class ProcessVideoJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout;

    public int $uniqueFor;

    public function __construct(public int $mediaId)
    {
        $this->timeout = (int) config('video.timeout', 600);
        $this->uniqueFor = $this->timeout + 300;
    }

    public function uniqueId(): string
    {
        return (string) $this->mediaId;
    }

    public function handle(VideoProcessor $processor): void
    {
        $media = Media::query()->find($this->mediaId);

        if ($media) {
            $processor->process($media);
        }
    }

    public function failed(?Throwable $exception): void
    {
        app(VideoProcessor::class)->fail(
            Media::query()->find($this->mediaId),
            $exception?->getMessage() === 'Video okunamadı.'
                ? 'Video okunamadı.'
                : 'Video işlenirken zaman doldu.',
        );
    }
}
