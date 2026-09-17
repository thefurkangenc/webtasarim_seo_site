<?php

namespace App\Captcha\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Laravel istisnanın kendi render() metodunu çağırır; bu sayede captcha
 * uçlarının hata sözleşmesi projenin exception handler'ına bağlı kalmaz ve
 * klasör olduğu gibi başka bir projeye taşınabilir.
 */
class CaptchaException extends RuntimeException
{
    public function render(Request $request): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $this->getMessage()], 422);
    }
}
