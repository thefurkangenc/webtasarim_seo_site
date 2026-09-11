<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Notification\NotificationReadRequest;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly NotificationService $service) {}

    public function index(): JsonResponse
    {
        return $this->success(data: $this->service->feed(auth()->user()));
    }

    public function read(NotificationReadRequest $request): JsonResponse
    {
        $this->service->markRead($request->user(), $request->validated('key'));

        return $this->success(data: $this->service->feed($request->user()));
    }

    public function readAll(): JsonResponse
    {
        $this->service->markAllRead(auth()->user());

        return $this->success('Tüm bildirimler okundu işaretlendi.', $this->service->feed(auth()->user()));
    }
}
