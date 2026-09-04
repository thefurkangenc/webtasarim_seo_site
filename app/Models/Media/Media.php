<?php

namespace App\Models\Media;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Table('media')]
#[Fillable([
    'folder_id', 'disk', 'path', 'original_path', 'name', 'original_name',
    'mime_type', 'extension', 'size', 'width', 'height', 'alt', 'title',
    'crop', 'conversions', 'preset', 'uploaded_by',
])]
class Media extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'crop' => 'array',
            'conversions' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Görüntülenebilir URL. Türetilmiş boyut yoksa ana dosyaya düşer.
     */
    public function url(?string $conversion = null): string
    {
        return Storage::disk($this->disk)->url($this->pathFor($conversion));
    }

    public function pathFor(?string $conversion = null): string
    {
        return $conversion ? ($this->conversions[$conversion] ?? $this->path) : $this->path;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /** SVG kırpılamaz ve dönüştürülemez; olduğu gibi saklanır. */
    public function isCroppable(): bool
    {
        return $this->isImage() && $this->extension !== 'svg';
    }

    /** Diskte tuttuğu tüm dosyalar — silme sırasında kullanılır. */
    public function allPaths(): array
    {
        return array_values(array_filter([
            $this->path,
            $this->original_path,
            ...array_values($this->conversions ?? []),
        ]));
    }

    /**
     * JS tarafına giden tek tip gövde. File manager, seçici modal ve
     * <x-admin::form.image> aynı şekli bekler.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alt' => $this->alt,
            'title' => $this->title,
            'url' => $this->url(),
            // Kütüphane ızgarası her zaman kare bir kart ister; 'thumb' bunun için
            // 400x400'e cover-crop edilir. Form alanı önizlemesi bunu kullanmamalı —
            // geniş/dar bir preset üzerinde ikinci bir kare kırpma, kullanıcının
            // modalda seçtiğinden çok daha kırpılmış görünmesine yol açar.
            'thumb' => $this->url('thumb'),
            // Oranı bozmadan küçültür (crop yok); form alanı önizlemesi bunu kullanır.
            'medium' => $this->url('medium'),
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'human_size' => $this->humanSize(),
            'width' => $this->width,
            'height' => $this->height,
            'is_image' => $this->isImage(),
            'is_croppable' => $this->isCroppable(),
            'preset' => $this->preset,
            'can_recrop' => (bool) $this->original_path,
            'folder_id' => $this->folder_id,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }

    public function humanSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = max($this->size, 0);
        $power = $size > 0 ? (int) floor(log($size, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return round($size / (1024 ** $power), $power > 1 ? 1 : 0).' '.$units[$power];
    }
}
