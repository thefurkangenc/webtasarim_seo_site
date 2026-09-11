<?php

namespace App\Services\Google;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Google service account kimliği — harici paket yok.
 *
 * Service account JSON'undan okunan özel anahtarla bir JWT imzalanır
 * (openssl_sign / RS256), Google'ın token uç noktasından erişim jetonu alınır
 * ve jeton kapsam (scope) başına 50 dakika cache'lenir.
 *
 * GA4 ve Search Console istemcileri aynı kimliği paylaşır; kullanıcı panele
 * tek bir JSON dosyası girer.
 *
 * Özel anahtar yalnızca bu nesnenin belleğinde tutulur; loglanmaz, cache'lenmez.
 */
class GoogleServiceAccount
{
    public function __construct(
        public readonly string $clientEmail,
        public readonly string $privateKey,
        public readonly string $tokenUri,
    ) {}

    /** @throws GoogleException JSON geçersizse. */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (! is_array($data) || blank($data['client_email'] ?? null) || blank($data['private_key'] ?? null)) {
            throw new GoogleException('Geçerli bir service account JSON dosyası değil (client_email / private_key eksik).');
        }

        return new self(
            (string) $data['client_email'],
            (string) $data['private_key'],
            (string) ($data['token_uri'] ?? 'https://oauth2.googleapis.com/token'),
        );
    }

    /** Verilen kapsam için erişim jetonu; 50 dk cache'lenir. */
    public function accessToken(string $scope): string
    {
        return Cache::remember($this->tokenCacheKey($scope), now()->addMinutes(50), function () use ($scope) {
            $now = time();

            $segments = $this->b64(['alg' => 'RS256', 'typ' => 'JWT']).'.'.$this->b64([
                'iss' => $this->clientEmail,
                'scope' => $scope,
                'aud' => $this->tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ]);

            $signature = '';

            if (! openssl_sign($segments, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
                throw new GoogleException('Service account özel anahtarıyla imzalama başarısız. JSON dosyasını kontrol edin.');
            }

            $response = Http::asForm()->timeout(20)->post($this->tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $segments.'.'.$this->b64url($signature),
            ]);

            if ($response->failed() || blank($response->json('access_token'))) {
                throw new GoogleException(self::errorMessage($response->json(), $response->status(), 'Google erişim jetonu alınamadı'));
            }

            return (string) $response->json('access_token');
        });
    }

    public function forgetToken(string $scope): void
    {
        Cache::forget($this->tokenCacheKey($scope));
    }

    /** Google'ın hata gövdesinden kullanıcıya gösterilebilir bir mesaj çıkarır. */
    public static function errorMessage(mixed $json, int $status, string $prefix): string
    {
        $detail = is_array($json)
            ? ($json['error']['message'] ?? $json['error_description'] ?? (is_string($json['error'] ?? null) ? $json['error'] : null))
            : null;

        return $detail ? "{$prefix}: {$detail}" : "{$prefix} (HTTP {$status}).";
    }

    private function tokenCacheKey(string $scope): string
    {
        return 'google.token.'.sha1($this->clientEmail.'|'.$scope);
    }

    /** @param  array<string, mixed>  $data */
    private function b64(array $data): string
    {
        return $this->b64url((string) json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function b64url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
