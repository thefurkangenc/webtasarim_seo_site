<?php

namespace App\Services\SearchConsole;

use App\Services\Google\GoogleServiceAccount;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Google Search Console API istemcisi — harici paket yok.
 *
 * Kimlik doğrulama paylaşılan GoogleServiceAccount'ta (GA4 ile aynı JSON).
 * Kapsam `webmasters` (salt okuma değil): site haritası gönderimi yazma ister.
 *
 * Uç noktalar:
 *   arama performansı  POST /webmasters/v3/sites/{site}/searchAnalytics/query
 *   site haritaları    GET|PUT|DELETE /webmasters/v3/sites/{site}/sitemaps[/{feed}]
 *   URL denetimi       POST /v1/urlInspection/index:inspect
 */
class SearchConsoleClient
{
    private const SCOPE = 'https://www.googleapis.com/auth/webmasters';

    private const BASE = 'https://searchconsole.googleapis.com';

    public function __construct(
        private readonly GoogleServiceAccount $account,
        private readonly string $siteUrl,
    ) {}

    /**
     * Service account'un erişebildiği siteler (Search Console'da kullanıcı
     * olarak eklenmiş olanlar).
     *
     * @return list<array{site_url: string, permission: string}>
     */
    public function sites(): array
    {
        $data = $this->send('get', '/webmasters/v3/sites');

        return array_map(fn (array $entry) => [
            'site_url' => (string) ($entry['siteUrl'] ?? ''),
            'permission' => (string) ($entry['permissionLevel'] ?? ''),
        ], $data['siteEntry'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return list<array{keys: list<string>, clicks: int, impressions: int, ctr: float, position: float}>
     */
    public function searchAnalytics(array $body): array
    {
        $data = $this->send('post', "/webmasters/v3/sites/{$this->site()}/searchAnalytics/query", $body);

        return array_map(fn (array $row) => [
            'keys' => array_map('strval', $row['keys'] ?? []),
            'clicks' => (int) round((float) ($row['clicks'] ?? 0)),
            'impressions' => (int) round((float) ($row['impressions'] ?? 0)),
            'ctr' => (float) ($row['ctr'] ?? 0),
            'position' => (float) ($row['position'] ?? 0),
        ], $data['rows'] ?? []);
    }

    /** @return list<array<string, mixed>> Search Console'a bildirilmiş site haritaları. */
    public function sitemaps(): array
    {
        return $this->send('get', "/webmasters/v3/sites/{$this->site()}/sitemaps")['sitemap'] ?? [];
    }

    public function submitSitemap(string $feedUrl): void
    {
        $this->send('put', "/webmasters/v3/sites/{$this->site()}/sitemaps/".rawurlencode($feedUrl));
    }

    public function deleteSitemap(string $feedUrl): void
    {
        $this->send('delete', "/webmasters/v3/sites/{$this->site()}/sitemaps/".rawurlencode($feedUrl));
    }

    /** @return array<string, mixed> */
    public function inspect(string $url): array
    {
        $data = $this->send('post', '/v1/urlInspection/index:inspect', [
            'inspectionUrl' => $url,
            'siteUrl' => $this->siteUrl,
            'languageCode' => 'tr',
        ]);

        return $data['inspectionResult'] ?? [];
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, array $body = []): array
    {
        $response = $this->request()->{$method}(self::BASE.$path, $body);

        if ($response->failed()) {
            throw new SearchConsoleException(GoogleServiceAccount::errorMessage(
                $response->json(),
                $response->status(),
                'Search Console isteği başarısız',
            ));
        }

        return $response->json() ?? [];
    }

    private function request(): PendingRequest
    {
        return Http::withToken($this->account->accessToken(self::SCOPE))->acceptJson()->timeout(25);
    }

    /** Site adresi yol parçası olarak gider; `sc-domain:` ve `https://` biçimleri de kodlanmalı. */
    private function site(): string
    {
        return rawurlencode($this->siteUrl);
    }
}
