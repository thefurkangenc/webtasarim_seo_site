<?php

namespace App\Http\Controllers\Admin\Popup;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Popup\PopupCreateRequest;
use App\Http\Requests\Admin\Popup\PopupFilterRequest;
use App\Http\Requests\Admin\Popup\PopupUpdateRequest;
use App\Models\Popup\Popup;
use App\Services\Popup\PopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PopupController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly PopupService $service) {}

    public function index(): View
    {
        return view('admin.pages.popup.index');
    }

    public function datatable(PopupFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function form(?Popup $popup = null): View
    {
        return view('admin.pages.popup.modals.form', $this->service->formData($popup));
    }

    public function store(PopupCreateRequest $request): JsonResponse
    {
        return $this->success('Açılır pencere eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(PopupUpdateRequest $request, Popup $popup): JsonResponse
    {
        return $this->success('Açılır pencere güncellendi.', $this->service->update($popup, $request->validated())->toPayload());
    }

    public function destroy(Popup $popup): JsonResponse
    {
        $this->service->delete($popup);

        return $this->success('Açılır pencere silindi.');
    }
}
