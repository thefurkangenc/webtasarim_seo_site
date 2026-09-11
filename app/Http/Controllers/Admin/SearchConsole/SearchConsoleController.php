<?php

namespace App\Http\Controllers\Admin\SearchConsole;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SearchConsole\SearchConsoleInspectRequest;
use App\Http\Requests\Admin\SearchConsole\SearchConsoleUpdateRequest;
use App\Services\SearchConsole\SearchConsoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchConsoleController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SearchConsoleService $service) {}

    public function index(): View
    {
        return view('admin.pages.search-console.index', $this->service->formData());
    }

    public function update(SearchConsoleUpdateRequest $request): JsonResponse
    {
        $this->service->saveSettings($request->validated());

        return $this->success('Search Console ayarları kaydedildi.');
    }

    public function sites(): JsonResponse
    {
        return $this->success(data: $this->service->sites());
    }

    public function performance(Request $request): JsonResponse
    {
        return $this->success(data: $this->service->performance((int) $request->query('range', 28)));
    }

    public function sitemaps(): JsonResponse
    {
        return $this->success(data: $this->service->sitemaps());
    }

    public function submit(): JsonResponse
    {
        $this->service->submitSitemap();

        return $this->success('Site haritası Search Console\'a bildirildi.');
    }

    public function inspect(SearchConsoleInspectRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->inspect($request->validated('url')));
    }

    public function test(): JsonResponse
    {
        $result = $this->service->test();

        return $result['ok']
            ? $this->success($result['message'])
            : $this->error($result['message']);
    }
}
