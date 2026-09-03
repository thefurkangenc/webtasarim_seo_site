<?php

namespace App\Http\Controllers\Admin\Media;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\MediaFilterRequest;
use App\Http\Requests\Admin\Media\MediaRecropRequest;
use App\Http\Requests\Admin\Media\MediaUpdateRequest;
use App\Http\Requests\Admin\Media\MediaUploadRequest;
use App\Models\Media\Media;
use App\Models\Media\MediaFolder;
use App\Services\Media\MediaFolderService;
use App\Services\Media\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MediaController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly MediaService $service,
        private readonly MediaFolderService $folders,
    ) {}

    public function index(): View
    {
        return view('admin.pages.media.index', ['folders' => $this->folders->tree()]);
    }

    /** Form içinden açılan seçici modalın gövdesi. */
    public function picker(): View
    {
        return view('admin.pages.media.modals.picker', ['folders' => $this->folders->tree()]);
    }

    /** Dosya bilgilerini düzenleme modalının gövdesi. */
    public function form(Media $media): View
    {
        return view('admin.pages.media.modals.form', [
            'media' => $media,
            'folderOptions' => MediaFolder::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function datatable(MediaFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function upload(MediaUploadRequest $request): JsonResponse
    {
        $media = $this->service->store($request->file('file'), $request->safe()->except('file'));

        return $this->success('Dosya yüklendi.', $media->toPayload());
    }

    public function update(MediaUpdateRequest $request, Media $media): JsonResponse
    {
        return $this->success(
            'Dosya güncellendi.',
            $this->service->update($media, $request->validated())->toPayload(),
        );
    }

    public function recrop(MediaRecropRequest $request, Media $media): JsonResponse
    {
        return $this->success(
            'Görsel yeniden kırpıldı.',
            $this->service->recrop($media, $request->validated('crop'))->toPayload(),
        );
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->service->delete($media);

        return $this->success('Dosya silindi.');
    }
}
