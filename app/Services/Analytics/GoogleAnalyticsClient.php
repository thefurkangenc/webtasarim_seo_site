<?php

namespace App\Services\Analytics;

use App\Services\Google\GoogleServiceAccount;
use Illuminate\Support\Facades\Http;

/**
 * Google Analytics Data API (GA4) istemcisi — harici paket yok.
 *
 * Kimlik doğrulama ve jeton cache'i paylaşılan GoogleServiceAccount'ta;
 * bu sınıf yalnızca rapor isteklerini analyticsdata.googleapis.com'a taşır.
 */
class GoogleAnalyticsClient
{
    private const SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';

    private const DATA_BASE = 'https://analyticsdata.googleapis.com/v1beta';

    public function __construct(
        private readonly GoogleServiceAccount $account,
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
        $this->account->forgetToken(self::SCOPE);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function post(string $method, array $body): array
    {
        $response = Http::withToken($this->account->accessToken(self::SCOPE))
            ->acceptJson()
            ->timeout(20)
            ->post(self::DATA_BASE.'/properties/'.$this->propertyId.$method, $body);

        if ($response->failed()) {
            throw new AnalyticsException(GoogleServiceAccount::errorMessage(
                $response->json(),
                $response->status(),
                'Google Analytics isteği başarısız',
            ));
        }

        return $response->json() ?? [];
    }
}
