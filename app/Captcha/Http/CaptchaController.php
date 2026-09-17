<?php

namespace App\Captcha\Http;

use App\Captcha\CaptchaManager;
use App\Captcha\Support\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CaptchaController extends Controller
{
    public function __construct(private readonly CaptchaManager $manager) {}

    public function challenge(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->manager->issue()]);
    }

    public function verify(SolveRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'ticket' => $this->manager->solveOrFail($request->validated('token'), $request->validated()),
            'expires_in' => (int) $this->manager->setting('ticket_ttl', 900),
        ]);
    }

    public function asset(string $file): BinaryFileResponse
    {
        return Asset::response($file);
    }
}
