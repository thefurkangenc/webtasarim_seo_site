<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Faq\FaqCreateRequest;
use App\Http\Requests\Admin\Faq\FaqFilterRequest;
use App\Http\Requests\Admin\Faq\FaqUpdateRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\Faq\Faq;
use App\Services\Faq\FaqService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FaqController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly FaqService $service) {}

    public function index(): View
    {
        return view('admin.pages.faq.index');
    }

    public function datatable(FaqFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function form(?Faq $faq = null): View
    {
        return view('admin.pages.faq.modals.form', $this->service->formData($faq));
    }

    public function store(FaqCreateRequest $request): JsonResponse
    {
        return $this->success('Soru eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(FaqUpdateRequest $request, Faq $faq): JsonResponse
    {
        return $this->success('Soru güncellendi.', $this->service->update($faq, $request->validated())->toPayload());
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $this->service->delete($faq);

        return $this->success('Soru silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
