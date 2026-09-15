<?php

namespace App\Http\Controllers\Admin\Slider;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\Slider\SliderCreateRequest;
use App\Http\Requests\Admin\Slider\SliderFilterRequest;
use App\Http\Requests\Admin\Slider\SliderUpdateRequest;
use App\Models\Slider\Slider;
use App\Services\Slider\SliderService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SliderController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SliderService $service) {}

    public function index(): View
    {
        return view('admin.pages.slider.index');
    }

    public function datatable(SliderFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function form(?Slider $slider = null): View
    {
        return view('admin.pages.slider.modals.form', $this->service->formData($slider?->load('media')));
    }

    public function store(SliderCreateRequest $request): JsonResponse
    {
        return $this->success('Slayt eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(SliderUpdateRequest $request, Slider $slider): JsonResponse
    {
        return $this->success('Slayt güncellendi.', $this->service->update($slider, $request->validated())->toPayload());
    }

    public function destroy(Slider $slider): JsonResponse
    {
        $this->service->delete($slider);

        return $this->success('Slayt silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
