<?php

namespace App\Http\Controllers\Admin\AiProvider;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AiProvider\AiProviderFilterRequest;
use App\Http\Requests\Admin\AiProvider\AiProviderRequest;
use App\Models\Ai\AiProvider;
use App\Services\Ai\AiProviderService;
use App\Services\Ai\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AiProviderController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly AiProviderService $service,
        private readonly AiService $ai,
    ) {}

    public function index(): View
    {
        return view('admin.pages.ai-provider.index', ['drivers' => config('ai.drivers')]);
    }

    public function form(?AiProvider $provider = null): View
    {
        return view('admin.pages.ai-provider.modals.form', [
            'provider' => $provider,
            'drivers' => config('ai.drivers'),
        ]);
    }

    public function datatable(AiProviderFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(AiProviderRequest $request): JsonResponse
    {
        return $this->success('Sağlayıcı eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(AiProviderRequest $request, AiProvider $provider): JsonResponse
    {
        return $this->success(
            'Sağlayıcı güncellendi.',
            $this->service->update($provider, $request->validated())->toPayload(),
        );
    }

    public function destroy(AiProvider $provider): JsonResponse
    {
        $this->service->delete($provider);

        return $this->success('Sağlayıcı silindi.');
    }

    /** Kaydedilmiş sağlayıcıya kısa bir istek atar. */
    public function test(AiProvider $provider): JsonResponse
    {
        $result = $this->ai->test($provider);

        return $this->success("Bağlantı başarılı ({$result['duration_ms']} ms).", $result);
    }
}
