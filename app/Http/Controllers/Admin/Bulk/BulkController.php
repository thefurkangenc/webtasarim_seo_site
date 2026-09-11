<?php

namespace App\Http\Controllers\Admin\Bulk;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Bulk\BulkRequest;
use App\Services\Bulk\BulkService;
use Illuminate\Http\JsonResponse;

/**
 * Tüm modüllerin toplu işlem ucu. Modül, route tanımındaki
 * `defaults('module', 'blog')` ile belirlenir; her modül kendi adresinde
 * (POST /admin/blog/bulk) kalır, izinler de kendi adıyla denetlenir.
 */
class BulkController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly BulkService $service) {}

    public function run(BulkRequest $request): JsonResponse
    {
        $result = $this->service->run(
            $request->module(),
            $request->validated('action'),
            $request->validated('ids'),
            $request->validated('value'),
        );

        return $this->success($result['message'], ['count' => $result['count']]);
    }
}
