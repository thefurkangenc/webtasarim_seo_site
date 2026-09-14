<?php

namespace App\Services\Media;

use App\Models\Media\Media;
use App\Support\Activity;
use App\Support\Ffmpeg;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Kütüphane videosundan poster, kalite kopyaları ve hover sprite üretir.
 *
 * Orijinal dosyaya dokunulmaz. Yukarı ölçekleme yok: kaynak 720 ise 1080
 * basamağı atlanır. Sprite düşerse kopyalar durur, hover yalnızca zaman olur.
 */
class VideoProcessor
{
    public function process(Media $media): void
    {
        if (! $media->isVideo()) {
            return;
        }

        try {
            $absolute = Storage::disk($media->disk)->path($media->path);
            $probe = $this->probe($absolute);

            $poster = $this->poster($media, $absolute, $probe['duration']);
            $renditions = $this->renditions($media, $absolute, $probe['height']);
            $sprite = $this->sprite($media, $absolute, $probe['duration'], $probe['width'], $probe['height']);

            $media->width = $probe['width'];
            $media->height = $probe['height'];
            $media->video = [
                'status' => 'ready',
                'duration' => $probe['duration'],
                'width' => $probe['width'],
                'height' => $probe['height'],
                'poster' => $poster,
                'sprite' => $sprite,
                'renditions' => $renditions,
                'error' => null,
            ];
            $media->saveQuietly();

            Activity::record(
                logName: 'media',
                event: 'processed',
                description: $media->name.' videosu işlendi.',
                subject: $media,
                subjectLabel: $media->name,
            );
        } catch (Throwable $exception) {
            $this->fail($media, $this->userMessage($exception));
        }
    }

    public function fail(?Media $media, string $message): void
    {
        if (! $media || ($media->video['status'] ?? null) === 'ready') {
            return;
        }

        $this->forgetDerived($media);

        $media->video = [
            ...($media->video ?? []),
            'status' => 'failed',
            'error' => $message,
        ];
        $media->saveQuietly();

        Activity::record(
            logName: 'media',
            event: 'failed',
            description: $media->name.' videosu işlenemedi.',
            subject: $media,
            subjectLabel: $media->name,
            properties: ['new' => ['error' => $message]],
            severity: 'warning',
        );
    }

    /** @return array{duration: float, width: int, height: int} */
    private function probe(string $absolute): array
    {
        $result = Process::timeout(60)->run([
            Ffmpeg::ffprobe() ?? config('video.ffprobe'),
            '-v', 'quiet',
            '-print_format', 'json',
            '-show_format',
            '-show_streams',
            $absolute,
        ]);

        if ($result->failed()) {
            throw new \RuntimeException('Video okunamadı.');
        }

        $payload = json_decode($result->output(), true);
        $stream = collect($payload['streams'] ?? [])->firstWhere('codec_type', 'video');

        $width = (int) ($stream['width'] ?? 0);
        $height = (int) ($stream['height'] ?? 0);
        $duration = (float) ($stream['duration'] ?? $payload['format']['duration'] ?? 0);

        if ($width < 2 || $height < 2 || $duration <= 0) {
            throw new \RuntimeException('Video okunamadı.');
        }

        return ['duration' => $duration, 'width' => $width, 'height' => $height];
    }

    /** @return array{path: string} */
    private function poster(Media $media, string $absolute, float $duration): array
    {
        $at = min((float) config('video.poster_at', 1), max(0, $duration - 0.05));
        $path = $this->sibling($media, 'poster.jpg');
        $output = $this->absolute($media, $path);

        $this->ffmpeg([
            '-ss', sprintf('%.3f', $at),
            '-i', $absolute,
            '-frames:v', '1',
            '-q:v', '3',
            $output,
        ]);

        return ['path' => $path];
    }

    /**
     * @return array<string, array{path: string, size: int}>
     */
    private function renditions(Media $media, string $absolute, int $height): array
    {
        $renditions = [];

        foreach (config('video.heights', [1080, 720, 480]) as $target) {
            $target = (int) $target;

            if ($height < $target) {
                continue;
            }

            $path = $this->sibling($media, $target.'.mp4');
            $output = $this->absolute($media, $path);
            $temp = $output.'.tmp';

            $this->ffmpeg([
                '-i', $absolute,
                '-vf', "scale=-2:{$target}",
                '-c:v', 'libx264',
                '-preset', 'medium',
                '-crf', (string) config('video.crf', 23),
                '-pix_fmt', 'yuv420p',
                '-movflags', '+faststart',
                '-c:a', 'aac',
                '-b:a', (string) config('video.audio_bitrate', '128k'),
                $temp,
            ]);

            $this->replace($temp, $output);

            $renditions[(string) $target] = [
                'path' => $path,
                'size' => Storage::disk($media->disk)->size($path),
            ];
        }

        return $renditions;
    }

    /** @return array<string, mixed>|null */
    private function sprite(Media $media, string $absolute, float $duration, int $width, int $height): ?array
    {
        $interval = (int) config('video.sprite.interval', 2);
        $frameWidth = (int) config('video.sprite.width', 160);
        $columns = (int) config('video.sprite.columns', 5);
        $frameHeight = max(2, (int) round($frameWidth * ($height / $width)));
        $frameHeight -= $frameHeight % 2;
        $frameWidth -= $frameWidth % 2;

        $frames = max(1, (int) ceil($duration / $interval));
        $rows = max(1, (int) ceil($frames / $columns));

        $path = $this->sibling($media, 'sprite.jpg');
        $vttPath = $this->sibling($media, 'sprite.vtt');
        $output = $this->absolute($media, $path);

        try {
            $this->ffmpeg([
                '-i', $absolute,
                '-vf', "fps=1/{$interval},scale={$frameWidth}:{$frameHeight},tile={$columns}x{$rows}",
                '-q:v', '4',
                $output,
            ]);
        } catch (Throwable) {
            return null;
        }

        Storage::disk($media->disk)->put($vttPath, $this->vtt($frames, $interval, $columns, $frameWidth, $frameHeight));

        return [
            'path' => $path,
            'vtt' => $vttPath,
            'interval' => $interval,
            'columns' => $columns,
            'width' => $frameWidth,
            'height' => $frameHeight,
        ];
    }

    /** @param  array<int, string>  $arguments */
    private function ffmpeg(array $arguments): void
    {
        $timeout = (int) config('video.timeout', 600);
        $result = Process::timeout($timeout)->run([
            Ffmpeg::ffmpeg() ?? config('video.ffmpeg'),
            '-y',
            ...$arguments,
        ]);

        if ($result->failed()) {
            throw new \RuntimeException($result->errorOutput() ?: 'Video işlenemedi.');
        }
    }

    private function sibling(Media $media, string $suffix): string
    {
        $directory = dirname($media->path);
        $uuid = pathinfo($media->path, PATHINFO_FILENAME);

        return "{$directory}/{$uuid}-{$suffix}";
    }

    private function absolute(Media $media, string $path): string
    {
        return Storage::disk($media->disk)->path($path);
    }

    private function replace(string $temp, string $final): void
    {
        if (! is_file($temp)) {
            throw new \RuntimeException('Video işlenemedi.');
        }

        if (is_file($final)) {
            unlink($final);
        }

        rename($temp, $final);
    }

    /** Orijinal hariç `{uuid}-*` türetilenleri siler (yarım encode / başarısız iş). */
    private function forgetDerived(Media $media): void
    {
        $disk = Storage::disk($media->disk);
        $prefix = pathinfo($media->path, PATHINFO_FILENAME).'-';

        foreach ($disk->files(dirname($media->path)) as $file) {
            if (str_starts_with(basename($file), $prefix)) {
                $disk->delete($file);
            }
        }
    }

    private function vtt(int $frames, int $interval, int $columns, int $width, int $height): string
    {
        $lines = ["WEBVTT\n"];

        for ($index = 0; $index < $frames; $index++) {
            $start = $index * $interval;
            $end = $start + $interval;
            $column = $index % $columns;
            $row = intdiv($index, $columns);
            $x = $column * $width;
            $y = $row * $height;

            $lines[] = $this->timestamp($start).' --> '.$this->timestamp($end);
            $lines[] = "xywh={$x},{$y},{$width},{$height}\n";
        }

        return implode("\n", $lines);
    }

    private function timestamp(float $seconds): string
    {
        $whole = (int) floor($seconds);
        $ms = (int) round(($seconds - $whole) * 1000);
        $h = intdiv($whole, 3600);
        $m = intdiv($whole % 3600, 60);
        $s = $whole % 60;

        return sprintf('%02d:%02d:%02d.%03d', $h, $m, $s, $ms);
    }

    private function userMessage(Throwable $exception): string
    {
        if ($exception instanceof ProcessTimedOutException) {
            return 'Video işlenirken zaman doldu.';
        }

        if (! Ffmpeg::installed()) {
            return 'Sunucuda ffmpeg yok.';
        }

        if ($exception->getMessage() === 'Video okunamadı.') {
            return 'Video okunamadı.';
        }

        return 'Video işlenemedi.';
    }
}
