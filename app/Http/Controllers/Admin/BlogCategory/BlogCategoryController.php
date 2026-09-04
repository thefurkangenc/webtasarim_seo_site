<?php

namespace App\Http\Controllers\Admin\BlogCategory;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategory\BlogCategoryCreateRequest;
use App\Http\Requests\Admin\BlogCategory\BlogCategoryFilterRequest;
use App\Http\Requests\Admin\BlogCategory\BlogCategoryUpdateRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\BlogCategory\BlogCategory;
use App\Services\BlogCategory\BlogCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly BlogCategoryService $service) {}

    public function index(): View
    {
        return view('admin.pages.blog-category.index');
    }

    public function form(?BlogCategory $category = null): View
    {
        return view('admin.pages.blog-category.modals.form', ['category' => $category?->load('seo.ogMedia')]);
    }

    public function datatable(BlogCategoryFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(BlogCategoryCreateRequest $request): JsonResponse
    {
        return $this->success('Kategori eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(BlogCategoryUpdateRequest $request, BlogCategory $category): JsonResponse
    {
        return $this->success(
            'Kategori güncellendi.',
            $this->service->update($category, $request->validated())->toPayload(),
        );
    }

    public function destroy(BlogCategory $category): JsonResponse
    {
        $this->service->delete($category);

        return $this->success('Kategori silindi.');
    }

    /** Sürükle-bırak sıralama modunun kaydettiği sıra. */
    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
