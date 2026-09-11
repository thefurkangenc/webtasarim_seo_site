<?php

namespace App\Http\Controllers\Admin\Revision;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Revision\RevisionFilterRequest;
use App\Models\Revision\Revision;
use App\Services\Revision\RevisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RevisionController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly RevisionService $service) {}

    public function index(): View
    {
        return view('admin.pages.revision.index', $this->service->indexData());
    }

    public function datatable(RevisionFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    /** Seçilen sürüm ile kaydın şu anki halinin karşılaştırması. */
    public function show(Revision $revision): JsonResponse
    {
        return $this->success(data: $this->service->detail($revision));
    }

    public function restore(Revision $revision): JsonResponse
    {
        $this->service->restore($revision);

        return $this->success('Kayıt bu sürüme döndürüldü.');
    }
}
