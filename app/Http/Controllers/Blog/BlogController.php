<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Services\Blog\BlogService;
use App\Support\SchemaContext;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(private readonly BlogService $service) {}

    public function show(string $slug): View
    {
        $blog = $this->service->findBySlug($slug);
        abort_unless($blog, 404);

        return view('pages.blog.show', [
            'blog' => $blog,
            'related' => $this->service->related($blog),
            'schemaContext' => SchemaContext::blogPosting($blog),
        ]);
    }

    public function index(): View
    {
        return view('pages.blog.index', [
            ...$this->service->listing(),
            'schemaContext' => SchemaContext::collection('Blog', route('blog')),
        ]);
    }

    /**
     * Kategori listesi. Kategoride yayında yazı yoksa sayfa boş durumla açılır
     * — 404 verilmez, kategori gerçekten var ve panelden yeniden doldurulabilir.
     */
    public function category(string $slug): View
    {
        $category = $this->service->findCategoryBySlug($slug);
        abort_unless($category, 404);

        return view('pages.blog.index', [
            ...$this->service->listing(category: $category),
            'schemaContext' => SchemaContext::collection($category->name, route('blog.kategori', $category->slug)),
        ]);
    }

    public function tag(string $slug): View
    {
        $tag = $this->service->findTagBySlug($slug);
        abort_unless($tag, 404);

        return view('pages.blog.index', [
            ...$this->service->listing(tag: $tag),
            'schemaContext' => SchemaContext::collection($tag->name, route('blog.etiket', $tag->slug)),
        ]);
    }
}
