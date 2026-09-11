<?php

namespace App\Http\Controllers\Admin\ProjectCategory;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectCategory\ProjectCategoryCreateRequest;
use App\Http\Requests\Admin\ProjectCategory\ProjectCategoryFilterRequest;
use App\Http\Requests\Admin\ProjectCategory\ProjectCategoryUpdateRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\ProjectCategory\ProjectCategory;
use App\Services\ProjectCategory\ProjectCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProjectCategoryController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ProjectCategoryService $service) {}

    public function index(): View
    {
        return view('admin.pages.project-category.index');
    }

    public function form(?ProjectCategory $category = null): View
    {
        return view('admin.pages.project-category.modals.form', ['category' => $category?->load('seo.ogMedia')]);
    }

    public function datatable(ProjectCategoryFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(ProjectCategoryCreateRequest $request): JsonResponse
    {
        return $this->success('Kategori eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(ProjectCategoryUpdateRequest $request, ProjectCategory $category): JsonResponse
    {
        return $this->success(
            'Kategori güncellendi.',
            $this->service->update($category, $request->validated())->toPayload(),
        );
    }

    public function destroy(ProjectCategory $category): JsonResponse
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
