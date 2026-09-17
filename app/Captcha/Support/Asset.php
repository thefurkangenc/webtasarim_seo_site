<?php

namespace App\Captcha\Support;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Bileşenin kendi css/js dosyaları. public/ altına kopyalanmaz, route
 * üzerinden sunulur — böylece klasör tek başına taşınabilir kalır ve yeni bir
 * projede "asset publish" adımı gerekmez. Adres içerik özetiyle sürümlendiği
 * için tarayıcıda kalıcı olarak önbelleğe alınabilir.
 */
final class Asset
{
    public const FILES = ['captcha.css', 'captcha.js'];

    private const TYPES = ['css' => 'text/css; charset=UTF-8', 'js' => 'application/javascript; charset=UTF-8'];

    /** @var array<string, string> */
    private static array $versions = [];

    public static function url(string $file): string
    {
        self::$versions[$file] ??= substr((string) md5_file(self::path($file)), 0, 8);

        return route('captcha.asset', ['file' => $file, 'v' => self::$versions[$file]]);
    }

    public static function response(string $file): BinaryFileResponse
    {
        return response()
            ->file(self::path($file), [
                'Content-Type' => self::TYPES[pathinfo($file, PATHINFO_EXTENSION)] ?? 'text/plain',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ])
            ->setAutoEtag();
    }

    private static function path(string $file): string
    {
        return dirname(__DIR__).'/resources/assets/'.$file;
    }
}
