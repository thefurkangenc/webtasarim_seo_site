<?php

namespace App\Http\Controllers\Admin\Search;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Search\GlobalSearchRequest;
use App\Services\Search\GlobalSearchService;
use Illuminate\Http\JsonResponse;

class GlobalSearchController extends Controller
{
    use RespondsWithJson;

    public function __invoke(GlobalSearchRequest $request, GlobalSearchService $service): JsonResponse
    {
        return $this->success(data: $service->search($request->validated('q') ?? '', $request->user()));
    }
}
