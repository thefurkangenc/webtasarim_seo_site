<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Media\Media;
use Illuminate\Http\JsonResponse;

/**
 * Oynatıcının kuyruk bitince kalite/sprite almak için sorduğu özet.
 * Yükleyen, klasör, orijinal ad yok — dosyalar zaten public diskte.
 */
class PlayerController extends Controller
{
    use RespondsWithJson;

    public function show(Media $media): JsonResponse
    {
        abort_unless($media->isVideo(), 404);

        return $this->success(data: $media->playerPayload());
    }
}
