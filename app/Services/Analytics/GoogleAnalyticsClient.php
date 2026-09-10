<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Google Analytics Data API (GA4) istemcisi — harici paket yok.
 *
 * Kimlik doğrulama: service account'un özel anahtarıyla bir JWT imzalanır
 * (openssl_sign / RS256), Google'ın token uç noktasından erişim jetonu alınır
 * ve jeton 50 dakika cache'lenir. Rapor istekleri analyticsdata.googleapis.com'a
 * gider.
 *
 * Özel anahtar yalnızca bu sınıfın belleğinde tutulur; loglanmaz, cache'lenmez.
 */
class GoogleAnalyticsClient
{
    private const SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';

    private const DATA_BASE = 'https://analyticsdata.googleapis.com/v1beta';

    public function __construct(
        private readonly string $clientEmail,
        private readonly string $privateKey,
        private readonly string $tokenUri,
        private readonly string $propertyId,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function runReport(array $body): array
    {
        return $this->post(':runReport', $body);
    }

    /**
     * @param  list<array<string, mixed>>  $requests
     * @return list<array<string, mixed>>
     */
    public function batchRunReports(array $requests): array
    {
        $data = $this->post(':batchRunReports', ['requests' => $requests]);
        $reports = $data['reports'] ?? [];

        if (count($reports) < count($requests)) {
            throw new AnalyticsException('Google Analytics beklenen sayıda rapor döndürmedi.');
        }

        return $reports;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function runRealtimeReport(array $body): array
    {
        return $this->post(':runRealtimeReport', $body);
    }

    /** Testlerde / anahtar değişince jetonu düşürmek için. */
    public function forgetToken(): void
    {
        Cache::forget($this->tokenCacheKey());
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function post(string $method, array $body): array
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(20)
            ->post(self::DATA_BASE.'/properties/'.$this->propertyId.$method, $body);

        if ($response->failed()) {
            throw new AnalyticsException($this->message($response->json(), $response->status()));
        }

        return $response->json() ?? [];
    }

    private function accessToken(): string
    {
        return Cache::remember($this->tokenCacheKey(), now()->addMinutes(50), function () {
            $now = time();

            $segments = $this->b64([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ]).'.'.$this->b64([
                'iss' => $this->clientEmail,
                'scope' => self::SCOPE,
                'aud' => $this->tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ]);

            $signature = '';

            if (! openssl_sign($segments, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
                throw new AnalyticsException('Service account özel anahtarıyla imzalama başarısız. JSON dosyasını kontrol edin.');
            }

            $assertion = $segments.'.'.$this->b64url($signature);

            $response = Http::asForm()->timeout(20)->post($this->tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            if ($response->failed() || blank($response->json('access_token'))) {
                throw new AnalyticsException($this->message($response->json(), $response->status(), 'Google erişim jetonu alınamadı'));
            }

            return (string) $response->json('access_token');
        });
    }

    private function tokenCacheKey(): string
    {
        return 'analytics.token.'.sha1($this->clientEmail.'|'.$this->propertyId);
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

    private function message(mixed $json, int $status, string $prefix = 'Google Analytics isteği başarısız'): string
    {
        $detail = is_array($json)
            ? ($json['error']['message'] ?? $json['error_description'] ?? (is_string($json['error'] ?? null) ? $json['error'] : null))
            : null;

        return $detail ? "{$prefix}: {$detail}" : "{$prefix} (HTTP {$status}).";
    }
}
