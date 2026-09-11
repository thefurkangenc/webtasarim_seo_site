<?php

namespace App\Http\Controllers\Admin\Health;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Services\Health\HealthService;
use App\Services\Health\QueueHealth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly HealthService $service,
        private readonly QueueHealth $queue,
    ) {}

    /** Rapor AJAX ile gelir: kontroller sayfa yüklenmesini bekletmez. */
    public function index(): View
    {
        return view('admin.pages.health.index');
    }

    /** Panelin yenileme ucu: ?fresh=1 kontrolleri baştan çalıştırır. */
    public function data(Request $request): JsonResponse
    {
        return $this->success(data: $this->service->indexData(
            fresh: $request->boolean('fresh'),
        ));
    }

    public function retry(string $uuid): JsonResponse
    {
        $this->queue->retry($uuid);

        return $this->success('İş yeniden kuyruğa alındı.');
    }

    public function retryAll(): JsonResponse
    {
        return $this->success($this->queue->retryAll().' iş yeniden kuyruğa alındı.');
    }

    public function forget(string $uuid): JsonResponse
    {
        $this->queue->forget($uuid);

        return $this->success('Kayıt silindi.');
    }

    public function flush(): JsonResponse
    {
        return $this->success($this->queue->flush().' kayıt silindi.');
    }
}
