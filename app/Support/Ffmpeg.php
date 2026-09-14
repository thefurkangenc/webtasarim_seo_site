<?php

namespace App\Support;

use Illuminate\Support\Facades\Process;
use Throwable;

/**
 * Sistemdeki ffmpeg / ffprobe ikiliğini bulur.
 *
 * PHP-FPM ve kuyruk işçisinin PATH'i (Herd, php-fpm) brew/apt yolunu
 * içermeyebilir; bu yüzden yapılandırılan yolun yanında yaygın konumlara
 * da bakılır. Paket kurulumu yapılmaz — yoksa kopyalanabilir komut döner.
 */
class Ffmpeg
{
    public static function ffmpeg(): ?string
    {
        return self::resolve((string) config('video.ffmpeg'), 'ffmpeg');
    }

    public static function ffprobe(): ?string
    {
        return self::resolve((string) config('video.ffprobe'), 'ffprobe');
    }

    public static function installed(): bool
    {
        return self::ffmpeg() !== null && self::ffprobe() !== null;
    }

    public static function installCommand(): string
    {
        return match (PHP_OS_FAMILY) {
            'Darwin' => 'brew install ffmpeg',
            'Linux' => self::linuxCommand(),
            default => 'https://ffmpeg.org/download.html',
        };
    }

    private static function linuxCommand(): string
    {
        if (is_file('/etc/alpine-release')) {
            return 'apk add --no-cache ffmpeg';
        }

        if (is_file('/etc/redhat-release') || is_file('/etc/fedora-release') || is_file('/etc/centos-release')) {
            return 'sudo dnf install -y ffmpeg';
        }

        return 'sudo apt-get update && sudo apt-get install -y ffmpeg';
    }

    private static function resolve(string $configured, string $name): ?string
    {
        static $cache = [];
        $key = $configured.'|'.$name;

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        foreach (self::candidates($configured, $name) as $candidate) {
            if (self::works($candidate)) {
                return $cache[$key] = $candidate;
            }
        }

        return $cache[$key] = null;
    }

    /** @return list<string> */
    private static function candidates(string $configured, string $name): array
    {
        $configured = trim($configured);
        $names = array_values(array_unique(array_filter([$configured, $name])));
        $dirs = ['/opt/homebrew/bin', '/usr/local/bin', '/usr/bin', '/bin'];
        $candidates = [];

        foreach ($names as $item) {
            $candidates[] = $item;

            if (! str_contains($item, DIRECTORY_SEPARATOR) && ! str_contains($item, '/')) {
                foreach ($dirs as $dir) {
                    $candidates[] = $dir.'/'.$item;
                }
            }
        }

        return array_values(array_unique($candidates));
    }

    private static function works(string $binary): bool
    {
        try {
            return Process::timeout(5)->run([$binary, '-version'])->successful();
        } catch (Throwable) {
            return false;
        }
    }
}
