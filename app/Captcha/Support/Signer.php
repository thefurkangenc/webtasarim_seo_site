<?php

namespace App\Captcha\Support;

use JsonException;

/**
 * Jetonları HMAC ile imzalar. Sunucu tarafında oturum ya da tablo tutmadan
 * "bu yükü ben ürettim ve süresi dolmadı" sorusunu cevaplar — tek kullanımlık
 * olma garantisi CaptchaManager'daki cache kaydından gelir.
 */
final class Signer
{
    public static function sign(array $payload, int $ttl): string
    {
        $payload['exp'] = time() + $ttl;

        try {
            $body = self::encode(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        } catch (JsonException) {
            return '';
        }

        return $body.'.'.self::encode(hash_hmac('sha256', $body, self::key(), true));
    }

    /** @return array<string, mixed>|null */
    public static function verify(string $token): ?array
    {
        [$body, $mac] = array_pad(explode('.', $token, 2), 2, null);

        if ($body === null || $mac === null) {
            return null;
        }

        if (! hash_equals(self::encode(hash_hmac('sha256', $body, self::key(), true)), $mac)) {
            return null;
        }

        $payload = json_decode((string) self::decode($body), true);

        if (! is_array($payload) || (int) ($payload['exp'] ?? 0) < time()) {
            return null;
        }

        return $payload;
    }

    private static function key(): string
    {
        $key = (string) (config('captcha.key') ?: config('app.key'));

        return $key !== '' ? $key : 'captcha-fallback-key';
    }

    private static function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function decode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/'), true);
    }
}
