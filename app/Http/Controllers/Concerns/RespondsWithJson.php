<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait RespondsWithJson
{
    /**
     * Başarılı yanıt.
     *
     * Paginator verildiğinde sayfalama bilgisi `meta` altına ayrılır; JS
     * tarafındaki DataTable bu yapıyı bekler.
     */
    protected function success(?string $message = null, mixed $data = null, int $status = 200): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if ($data instanceof LengthAwarePaginator) {
            $payload['data'] = $data->items();
            $payload['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];
        } else {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    /**
     * İş kuralı hatası. Doğrulama hataları için kullanılmaz —
     * onları FormRequest'in kendisi 422 + `errors` ile döndürür.
     */
    protected function error(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
