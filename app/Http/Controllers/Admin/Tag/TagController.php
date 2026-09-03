<?php

namespace App\Http\Controllers\Admin\Tag;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\TagSearchRequest;
use App\Services\Tag\TagService;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly TagService $service) {}

    /** Etiket alanının öneri listesi. */
    public function search(TagSearchRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->search($request->validated('q')));
    }
}
