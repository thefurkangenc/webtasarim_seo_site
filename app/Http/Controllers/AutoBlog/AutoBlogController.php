<?php

namespace App\Http\Controllers\AutoBlog;

use App\Http\Controllers\Controller;
use App\Services\AutoBlog\AutoBlogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutoBlogController extends Controller
{
    public function __construct(private readonly AutoBlogService $service) {}

    /**
     * Cron bu adresi çağırır. Anahtar adresin içindedir; eşleşmezse ya da
     * config'te tanımlı değilse uç nokta hiç yokmuş gibi davranır.
     */
    public function generate(Request $request, string $secret): JsonResponse
    {
        $expected = (string) config('auto-blog.secret');

        abort_if($expected === '' || ! hash_equals($expected, $secret), 404);

        $blog = $this->service->generate($request->only(['keywords', 'title', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Yazı oluşturuldu.',
            'data' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'status' => $blog->status,
                'has_cover' => $blog->getFirstMedia('cover') !== null,
            ],
        ]);
    }
}
