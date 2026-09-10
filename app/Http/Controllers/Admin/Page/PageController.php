<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Page\PageCreateRequest;
use App\Http\Requests\Admin\Page\PageFilterRequest;
use App\Http\Requests\Admin\Page\PageUpdateRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\Page\Page;
use App\Services\Page\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly PageService $service) {}

    public function index(): View
    {
        return view('admin.pages.page.index', ['parents' => $this->service->parentOptions()]);
    }

    public function create(): View
    {
        return view('admin.pages.page.form', [
            'page' => null,
            'parents' => $this->service->parentOptions(),
            'parentPaths' => $this->service->parentPaths(),
        ]);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.page.form', [
            'page' => $page->load(['tags', 'media', 'seo.ogMedia', 'faqs', 'parent']),
            'parents' => $this->service->parentOptions($page),
            'parentPaths' => $this->service->parentPaths(),
        ]);
    }

    public function datatable(PageFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(PageCreateRequest $request): JsonResponse
    {
        $page = $this->service->create($request->validated());

        return $this->success('Sayfa eklendi.', [
            ...$page->toPayload(),
            'redirect' => route('admin.page.edit', $page),
        ]);
    }

    public function update(PageUpdateRequest $request, Page $page): JsonResponse
    {
        return $this->success('Sayfa güncellendi.', $this->service->update($page, $request->validated())->toPayload());
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->service->delete($page);

        return $this->success('Sayfa silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
