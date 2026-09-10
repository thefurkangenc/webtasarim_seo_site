<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly AnalyticsService $service) {}

    public function index(): View
    {
        return view('admin.pages.analytics.index', [
            'configured' => $this->service->configured(),
            'clientEmail' => $this->service->clientEmail(),
            'propertyId' => $this->service->propertyId(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return $this->success(data: $this->service->summary((int) $request->query('range', 28)));
    }

    public function realtime(): JsonResponse
    {
        return $this->success(data: $this->service->realtime());
    }

    public function test(): JsonResponse
    {
        $result = $this->service->test();

        return $result['ok']
            ? $this->success($result['message'])
            : $this->error($result['message']);
    }
}
