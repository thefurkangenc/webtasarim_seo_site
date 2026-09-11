<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriber\SubscribeRequest;
use App\Services\Subscriber\SubscriberService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SubscriberService $service) {}

    public function store(SubscribeRequest $request): JsonResponse
    {
        $this->service->subscribe($request->validated(), $request->ip());

        return $this->success('Aboneliğiniz alındı. Teşekkürler.');
    }

    public function unsubscribe(string $token): View
    {
        $subscriber = $this->service->unsubscribe($token);

        return view('pages.subscriber.unsubscribed', [
            'email' => $subscriber->email,
        ]);
    }
}
