<?php

namespace App\Http\Controllers\Quote;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quote\QuoteSubmitRequest;
use App\Services\Quote\QuoteService;
use Illuminate\Http\JsonResponse;

class QuoteController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly QuoteService $service) {}

    public function store(QuoteSubmitRequest $request): JsonResponse
    {
        $this->service->submit($request->validated(), $request->ip(), $request->userAgent(), $request->headers->get('referer'));

        return $this->success('Talebiniz alındı. En kısa sürede sizi arayacağız.');
    }
}
