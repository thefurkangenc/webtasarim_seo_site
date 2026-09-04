<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Integration\IntegrationToggleRequest;
use App\Http\Requests\Admin\Integration\IntegrationUpdateRequest;
use App\Services\Integration\IntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly IntegrationService $service) {}

    public function form(string $key): View
    {
        return view('admin.pages.setting.modals.integration', $this->service->formData($key));
    }

    public function update(IntegrationUpdateRequest $request, string $key): JsonResponse
    {
        return $this->success('Entegrasyon kaydedildi.', $this->service->update($key, $request->validated()));
    }

    public function toggle(IntegrationToggleRequest $request, string $key): JsonResponse
    {
        $enabled = $request->boolean('enabled');

        return $this->success(
            $enabled ? 'Entegrasyon açıldı.' : 'Entegrasyon kapatıldı.',
            $this->service->toggle($key, $enabled),
        );
    }
}
