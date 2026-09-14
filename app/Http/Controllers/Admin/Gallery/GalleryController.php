<?php

namespace App\Http\Controllers\Admin\Gallery;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Gallery\GalleryCreateRequest;
use App\Http\Requests\Admin\Gallery\GalleryFilterRequest;
use App\Http\Requests\Admin\Gallery\GalleryUpdateRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\Gallery\Gallery;
use App\Services\Gallery\GalleryService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class GalleryController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly GalleryService $service) {}

    public function index(): View
    {
        return view('admin.pages.gallery.index', [
            'stats' => $this->service->stats(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.gallery.form', $this->service->formData(null));
    }

    public function edit(Gallery $gallery): View
    {
        return view('admin.pages.gallery.form', $this->service->formData($gallery));
    }

    public function datatable(GalleryFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(GalleryCreateRequest $request): JsonResponse
    {
        $gallery = $this->service->create($request->validated());

        return $this->success('Galeri eklendi.', [
            ...$gallery->toPayload(),
            'redirect' => route('admin.gallery.edit', $gallery),
        ]);
    }

    public function update(GalleryUpdateRequest $request, Gallery $gallery): JsonResponse
    {
        return $this->success('Galeri güncellendi.', $this->service->update($gallery, $request->validated())->toPayload());
    }

    public function destroy(Gallery $gallery): JsonResponse
    {
        $this->service->delete($gallery);

        return $this->success('Galeri silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
