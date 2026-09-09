<?php

namespace App\Http\Controllers\Admin\Service;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\Service\ServiceCreateRequest;
use App\Http\Requests\Admin\Service\ServiceFilterRequest;
use App\Http\Requests\Admin\Service\ServiceUpdateRequest;
use App\Models\Service\Service;
use App\Models\ServiceRegion\ServiceRegion;
use App\Services\Service\ServiceService;
use App\Support\Tree;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ServiceService $service) {}

    public function index(): View
    {
        return view('admin.pages.service.index', ['regions' => $this->regionOptions()]);
    }

    public function create(): View
    {
        return view('admin.pages.service.form', [
            'service' => null,
            'regions' => $this->regionOptions(),
            'cityIds' => $this->cityIds(),
        ]);
    }

    public function edit(Service $service): View
    {
        return view('admin.pages.service.form', [
            'service' => $service->load(['tags', 'media', 'seo.ogMedia', 'regions', 'faqs']),
            'regions' => $this->regionOptions(),
            'cityIds' => $this->cityIds(),
        ]);
    }

    public function datatable(ServiceFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(ServiceCreateRequest $request): JsonResponse
    {
        $service = $this->service->create($request->validated());

        return $this->success('Hizmet eklendi.', [
            ...$service->toPayload(),
            'redirect' => route('admin.service.edit', $service),
        ]);
    }

    public function update(ServiceUpdateRequest $request, Service $service): JsonResponse
    {
        return $this->success('Hizmet güncellendi.', $this->service->update($service, $request->validated())->toPayload());
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->service->delete($service);

        return $this->success('Hizmet silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }

    /**
     * Bölgeler çoklu select'te ağaç (girintili) görünümde sunulur — bir ilin
     * altındaki ilçe soldan boşluk + ikonla onun çocuğu gibi görünür.
     *
     * @return array<int, array{label: string, depth: int}>
     */
    private function regionOptions(): array
    {
        return Tree::options(
            ServiceRegion::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'parent_id', 'name']),
        );
    }

    /**
     * Formdaki "Tüm illeri seç" kısayolunun hangi seçenekleri işaretleyeceği.
     * İl = kökteki bölge (parent_id boş).
     *
     * @return array<int, int>
     */
    private function cityIds(): array
    {
        return ServiceRegion::whereNull('parent_id')
            ->where('is_active', true)
            ->pluck('id')
            ->all();
    }
}
