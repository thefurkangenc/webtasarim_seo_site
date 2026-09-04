<?php

namespace App\Support;

class Consent
{
    public const COOKIE = 'cookie_consent';

    /**
     * Çubuk kapalıysa tüm kategoriler açık kabul edilir — mevcut izleme
     * davranışı bozulmasın. Açık ve karar yoksa isteğe bağlılar kapalıdır.
     *
     * @return array{banner: bool, decided: bool, necessary: bool, functional: bool, analytics: bool, marketing: bool}
     */
    public static function snapshot(): array
    {
        if (! Settings::bool('cookie.enabled')) {
            return [
                'banner' => false,
                'decided' => true,
                'necessary' => true,
                'functional' => true,
                'analytics' => true,
                'marketing' => true,
            ];
        }

        $empty = [
            'banner' => true,
            'decided' => false,
            'necessary' => true,
            'functional' => false,
            'analytics' => false,
            'marketing' => false,
        ];

        $raw = request()->cookie(self::COOKIE);

        if (! is_string($raw) || $raw === '') {
            return $empty;
        }

        $data = json_decode($raw, true);

        if (! is_array($data)) {
            return $empty;
        }

        $version = (int) (Settings::merged('cookie')['version'] ?? 1);

        if ((int) ($data['v'] ?? 0) !== $version) {
            return $empty;
        }

        return [
            'banner' => true,
            'decided' => true,
            'necessary' => true,
            'functional' => (bool) ($data['functional'] ?? false),
            'analytics' => (bool) ($data['analytics'] ?? false),
            'marketing' => (bool) ($data['marketing'] ?? false),
        ];
    }

    public static function allows(string $category): bool
    {
        return (bool) (self::snapshot()[$category] ?? false);
    }
}
