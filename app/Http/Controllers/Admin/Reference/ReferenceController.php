<?php

namespace App\Http\Controllers\Admin\Reference;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reference\ReferenceCreateRequest;
use App\Http\Requests\Admin\Reference\ReferenceFilterRequest;
use App\Http\Requests\Admin\Reference\ReferenceUpdateRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\Reference\Reference;
use App\Services\Reference\ReferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ReferenceService $service) {}

    public function index(): View
    {
        return view('admin.pages.reference.index');
    }

    public function datatable(ReferenceFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function form(?Reference $reference = null): View
    {
        return view('admin.pages.reference.modals.form', $this->service->formData($reference?->load('media')));
    }

    public function store(ReferenceCreateRequest $request): JsonResponse
    {
        return $this->success('Referans eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(ReferenceUpdateRequest $request, Reference $reference): JsonResponse
    {
        return $this->success(
            'Referans güncellendi.',
            $this->service->update($reference, $request->validated())->toPayload(),
        );
    }

    public function destroy(Reference $reference): JsonResponse
    {
        $this->service->delete($reference);

        return $this->success('Referans silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
