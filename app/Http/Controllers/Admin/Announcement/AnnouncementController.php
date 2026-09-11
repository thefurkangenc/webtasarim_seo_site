<?php

namespace App\Http\Controllers\Admin\Announcement;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Announcement\AnnouncementCreateRequest;
use App\Http\Requests\Admin\Announcement\AnnouncementFilterRequest;
use App\Http\Requests\Admin\Announcement\AnnouncementUpdateRequest;
use App\Models\Announcement\Announcement;
use App\Services\Announcement\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly AnnouncementService $service) {}

    public function index(): View
    {
        return view('admin.pages.announcement.index');
    }

    public function datatable(AnnouncementFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function form(?Announcement $announcement = null): View
    {
        return view('admin.pages.announcement.modals.form', $this->service->formData($announcement));
    }

    public function store(AnnouncementCreateRequest $request): JsonResponse
    {
        return $this->success('Duyuru eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(AnnouncementUpdateRequest $request, Announcement $announcement): JsonResponse
    {
        return $this->success('Duyuru güncellendi.', $this->service->update($announcement, $request->validated())->toPayload());
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->service->delete($announcement);

        return $this->success('Duyuru silindi.');
    }
}
