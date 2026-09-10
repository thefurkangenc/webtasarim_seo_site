<?php

namespace App\Http\Controllers\Admin\ActivityLog;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivityLog\ActivityLogFilterRequest;
use App\Models\ActivityLog\ActivityLog;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ActivityLogService $service) {}

    public function index(): View
    {
        return view('admin.pages.activity-log.index', [
            'stats' => $this->service->stats(),
            'options' => $this->service->filterOptions(),
        ]);
    }

    public function datatable(ActivityLogFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    /** Detay modalının içeriği — liste yükünde taşınmayan tüm alanlar. */
    public function show(ActivityLog $activityLog): JsonResponse
    {
        return $this->success(data: $this->service->detail($activityLog));
    }
}
