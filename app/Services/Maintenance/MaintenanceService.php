<?php

namespace App\Services\Maintenance;

use App\Models\Media\Media;
use App\Support\Settings;
use Illuminate\Http\Response;

class MaintenanceService
{
    /** @return array<string, mixed> */
    public function pageData(): array
    {
        $values = Settings::merged('maintenance');
        $company = Settings::group('company');
        $logoId = $company['logo_media_id'] ?? null;
        $logo = $logoId ? Media::query()->find((int) $logoId)?->url('medium') : null;

        return [
            'title' => $values['title'] ?: 'Kısa bir ara veriyoruz',
            'message' => $values['message'] ?: 'Sitemiz şu anda güncelleniyor.',
            'company_name' => $company['name'] ?? config('app.name'),
            'logo' => $logo,
            'retry_after' => filled($values['retry_after'] ?? null) ? (int) $values['retry_after'] : null,
        ];
    }

    public function response(): Response
    {
        $page = $this->pageData();
        $response = response()->view('pages.maintenance', $page, 503);

        if ($page['retry_after']) {
            $response->headers->set('Retry-After', (string) ($page['retry_after'] * 60));
        }

        return $response;
    }

    public function validSecret(string $secret): bool
    {
        $stored = (string) (Settings::get('maintenance.bypass_secret') ?? '');

        return $stored !== '' && $secret !== '' && hash_equals($stored, $secret);
    }

    public function cookieValue(string $secret): string
    {
        return hash('sha256', $secret);
    }
}
