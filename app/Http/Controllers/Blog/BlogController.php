<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Services\Blog\BlogService;
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
        ]);
    }
}
