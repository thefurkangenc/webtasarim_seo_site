<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\BlogCreateRequest;
use App\Http\Requests\Admin\Blog\BlogFilterRequest;
use App\Http\Requests\Admin\Blog\BlogUpdateRequest;
use App\Models\Blog\Blog;
use App\Models\BlogCategory\BlogCategory;
use App\Services\Blog\BlogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BlogController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly BlogService $service) {}

    public function index(): View
    {
        return view('admin.pages.blog.index', ['categories' => $this->categories()]);
    }

    public function create(): View
    {
        return view('admin.pages.blog.form', ['blog' => null, 'categories' => $this->categories()]);
    }

    public function edit(Blog $blog): View
    {
        return view('admin.pages.blog.form', [
            'blog' => $blog->load(['tags', 'media', 'seo.ogMedia', 'faqs']),
            'categories' => $this->categories(),
        ]);
    }

    public function datatable(BlogFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(BlogCreateRequest $request): JsonResponse
    {
        $blog = $this->service->create($request->validated());

        return $this->success('Yazı eklendi.', [
            ...$blog->toPayload(),
            'redirect' => route('admin.blog.edit', $blog),
        ]);
    }

    public function update(BlogUpdateRequest $request, Blog $blog): JsonResponse
    {
        return $this->success('Yazı güncellendi.', $this->service->update($blog, $request->validated())->toPayload());
    }

    public function destroy(Blog $blog): JsonResponse
    {
        $this->service->delete($blog);

        return $this->success('Yazı silindi.');
    }

    private function categories(): Collection
    {
        return BlogCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('name', 'id');
    }
}
