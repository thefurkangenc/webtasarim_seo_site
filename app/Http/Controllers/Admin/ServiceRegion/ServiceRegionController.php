<?php

namespace App\Http\Controllers\Admin\ServiceRegion;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\ServiceRegion\ServiceRegionCreateRequest;
use App\Http\Requests\Admin\ServiceRegion\ServiceRegionFilterRequest;
use App\Http\Requests\Admin\ServiceRegion\ServiceRegionUpdateRequest;
use App\Models\ServiceRegion\ServiceRegion;
use App\Services\ServiceRegion\ServiceRegionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceRegionController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ServiceRegionService $service) {}

    public function index(): View
    {
        return view('admin.pages.service-region.index');
    }

    /**
     * Modal gövdesi. Yeni kayıtta üst bölge kırılımdan gelir (`?parent_id=`),
     * düzenlemede kayıt kendi üstünü taşır.
     */
    public function form(Request $request, ?ServiceRegion $region = null): View
    {
        $parent = $region
            ? $region->parent
            : ServiceRegion::find($request->integer('parent_id'));

        return view('admin.pages.service-region.modals.form', [
            'region' => $region,
            'parent' => $parent,
        ]);
    }

    public function datatable(ServiceRegionFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    /** Kırılım başlığı: kökten seçili bölgeye kadarki zincir. */
    public function breadcrumb(ServiceRegion $region): JsonResponse
    {
        return $this->success(data: $this->service->breadcrumb($region));
    }

    public function store(ServiceRegionCreateRequest $request): JsonResponse
    {
        return $this->success('Bölge eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(ServiceRegionUpdateRequest $request, ServiceRegion $region): JsonResponse
    {
        return $this->success(
            'Bölge güncellendi.',
            $this->service->update($region, $request->validated())->toPayload(),
        );
    }

    public function destroy(ServiceRegion $region): JsonResponse
    {
        $this->service->delete($region);

        return $this->success('Bölge silindi.');
    }

    /** Sürükle-bırak sıralama modunun kaydettiği sıra. */
    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
