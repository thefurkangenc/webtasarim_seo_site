<?php

namespace App\Http\Controllers\Admin\Media;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\MediaBulkDeleteRequest;
use App\Http\Requests\Admin\Media\MediaBulkMoveRequest;
use App\Http\Requests\Admin\Media\MediaFilterRequest;
use App\Http\Requests\Admin\Media\MediaRecropRequest;
use App\Http\Requests\Admin\Media\MediaUpdateRequest;
use App\Http\Requests\Admin\Media\MediaUploadRequest;
use App\Models\Media\Media;
use App\Models\Media\MediaFolder;
use App\Services\Media\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MediaController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly MediaService $service) {}

    public function index(): View
    {
        return view('admin.pages.media.index');
    }

    /** Form içinden açılan seçici modalın gövdesi. */
    public function picker(): View
    {
        return view('admin.pages.media.modals.picker');
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

    /** Sidebar'daki depolama özeti. */
    public function stats(): JsonResponse
    {
        return $this->success(data: $this->service->stats());
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

    public function bulkMove(MediaBulkMoveRequest $request): JsonResponse
    {
        $result = $this->service->bulkMove(
            $request->validated('media', []),
            $request->validated('folders', []),
            $request->validated('target_folder_id'),
        );

        return $this->success($this->bulkMessage($result['moved'], 'taşındı', $result['skipped']), $result);
    }

    public function bulkDelete(MediaBulkDeleteRequest $request): JsonResponse
    {
        $result = $this->service->bulkDelete(
            $request->validated('media', []),
            $request->validated('folders', []),
        );

        return $this->success($this->bulkMessage($result['deleted'], 'silindi', $result['skipped']), $result);
    }

    /** @param  array<int, array{name: string, reason: string}>  $skipped */
    private function bulkMessage(int $count, string $verb, array $skipped): string
    {
        $message = "{$count} öğe {$verb}.";

        if ($skipped !== []) {
            $message .= ' '.count($skipped).' öğe atlandı: '.$skipped[0]['reason'];
        }

        return $message;
    }
}
