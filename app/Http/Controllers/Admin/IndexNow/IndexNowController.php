<?php

namespace App\Http\Controllers\Admin\IndexNow;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexNow\IndexNowSubmitRequest;
use App\Http\Requests\Admin\IndexNow\IndexNowUpdateRequest;
use App\Services\IndexNow\IndexNowService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class IndexNowController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly IndexNowService $service) {}

    public function index(): View
    {
        return view('admin.pages.indexnow.index', $this->service->formData());
    }

    public function update(IndexNowUpdateRequest $request): JsonResponse
    {
        $this->service->saveSettings($request->validated());

        return $this->success('Ayarlar kaydedildi.');
    }

    public function submit(IndexNowSubmitRequest $request): JsonResponse
    {
        $result = $this->service->submit($request->urls());

        return $this->success($result['message']);
    }

    public function submitAll(): JsonResponse
    {
        return $this->success($this->service->submitAll()['message']);
    }

    public function regenerateKey(): JsonResponse
    {
        return $this->success('Yeni anahtar üretildi.', ['key' => $this->service->regenerateKey()]);
    }
}
