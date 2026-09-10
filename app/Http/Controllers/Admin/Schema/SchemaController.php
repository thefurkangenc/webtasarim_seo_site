<?php

namespace App\Http\Controllers\Admin\Schema;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Schema\SchemaPreviewRequest;
use App\Services\Schema\SchemaInspector;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SchemaController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SchemaInspector $inspector) {}

    public function index(): View
    {
        return view('admin.pages.schema.index', [
            'samples' => $this->inspector->samples(),
        ]);
    }

    public function preview(SchemaPreviewRequest $request): JsonResponse
    {
        return $this->success(data: $this->inspector->report($request->validated('url')));
    }
}
