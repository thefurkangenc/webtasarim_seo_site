<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Services\Seo\SeoHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoHealthController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SeoHealthService $service) {}

    public function index(): View
    {
        return view('admin.pages.seo.index', [
            'overview' => $this->service->overview(),
            'tabs' => SeoHealthService::TABS,
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $tab = (string) $request->query('tab', 'low_score');

        abort_unless(array_key_exists($tab, SeoHealthService::TABS), 404);

        return $this->success(data: $this->service->report($tab));
    }

    public function rescore(): JsonResponse
    {
        $count = $this->service->rescore();

        return $this->success("{$count} içerik yeniden puanlandı.", $this->service->overview());
    }
}
