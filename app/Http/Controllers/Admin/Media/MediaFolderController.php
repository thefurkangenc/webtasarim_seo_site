<?php

namespace App\Http\Controllers\Admin\Media;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\MediaFolderCreateRequest;
use App\Http\Requests\Admin\Media\MediaFolderUpdateRequest;
use App\Models\Media\MediaFolder;
use App\Services\Media\MediaFolderService;
use Illuminate\Http\JsonResponse;

class MediaFolderController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly MediaFolderService $service) {}

    public function index(): JsonResponse
    {
        return $this->success(data: $this->service->tree());
    }

    public function store(MediaFolderCreateRequest $request): JsonResponse
    {
        return $this->success('Klasör oluşturuldu.', $this->service->create($request->validated()));
    }

    public function update(MediaFolderUpdateRequest $request, MediaFolder $folder): JsonResponse
    {
        return $this->success('Klasör güncellendi.', $this->service->update($folder, $request->validated()));
    }

    public function destroy(MediaFolder $folder): JsonResponse
    {
        $this->service->delete($folder);

        return $this->success('Klasör silindi.');
    }
}
