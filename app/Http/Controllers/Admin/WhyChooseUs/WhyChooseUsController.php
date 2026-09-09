<?php

namespace App\Http\Controllers\Admin\WhyChooseUs;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\WhyChooseUs\WhyChooseUsCreateRequest;
use App\Http\Requests\Admin\WhyChooseUs\WhyChooseUsFilterRequest;
use App\Http\Requests\Admin\WhyChooseUs\WhyChooseUsHeadingRequest;
use App\Http\Requests\Admin\WhyChooseUs\WhyChooseUsUpdateRequest;
use App\Models\WhyChooseUs\WhyChooseUs;
use App\Services\Setting\SettingService;
use App\Services\WhyChooseUs\WhyChooseUsService;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class WhyChooseUsController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly WhyChooseUsService $service,
        private readonly SettingService $settingService,
    ) {}

    public function index(): View
    {
        return view('admin.pages.why-choose-us.index', [
            'heading' => Settings::group('why_choose_us'),
        ]);
    }

    public function datatable(WhyChooseUsFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function form(?WhyChooseUs $why_choose_us = null): View
    {
        return view('admin.pages.why-choose-us.modals.form', $this->service->formData($why_choose_us));
    }

    public function store(WhyChooseUsCreateRequest $request): JsonResponse
    {
        return $this->success('Kayıt eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(WhyChooseUsUpdateRequest $request, WhyChooseUs $why_choose_us): JsonResponse
    {
        return $this->success('Kayıt güncellendi.', $this->service->update($why_choose_us, $request->validated())->toPayload());
    }

    public function destroy(WhyChooseUs $why_choose_us): JsonResponse
    {
        $this->service->delete($why_choose_us);

        return $this->success('Kayıt silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }

    public function updateHeading(WhyChooseUsHeadingRequest $request): JsonResponse
    {
        $this->settingService->putGroup('why_choose_us', $request->validated());

        return $this->success('Bölüm başlığı güncellendi.');
    }
}
