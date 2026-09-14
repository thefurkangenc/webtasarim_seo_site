<?php

namespace App\Models\Media;

use App\Models\Concerns\LogsActivity;
use App\Models\User;
use App\Support\MediaType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Table('media')]
#[Fillable([
    'folder_id', 'disk', 'path', 'original_path', 'name', 'original_name',
    'mime_type', 'extension', 'size', 'width', 'height', 'alt', 'title',
    'crop', 'conversions', 'video', 'preset', 'uploaded_by',
])]
class Media extends Model
{
    use LogsActivity;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'crop' => 'array',
            'conversions' => 'array',
            'video' => 'array',
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

    /**
     * Yeniden kırpma her zaman saklanan orijinal üzerinden yapılır — `path`
     * daha önceki kırpımın SONUCUdur, yeniden kırpma kaynağı olarak
     * kullanılırsa her seferinde biraz daha fazla kırpar. `original_path`
     * yoksa (SVG, ham dosya) ana dosyaya düşer.
     */
    public function originalUrl(): string
    {
        return Storage::disk($this->disk)->url($this->original_path ?: $this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Video dosyaları işlenmeden saklanır: küçük boyut (thumb/medium) üretilmez,
     * bu yüzden onları `<img>` içine koyan her yer önce bunu sormak zorundadır —
     * aksi halde kırık görsel ikonu basar.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Kaydın tür grubu: image / video / document / other. Alan kısıtı
     * (yalnızca görsel seçilebilen alanlar) ve kütüphane filtresi bunu okur.
     */
    public function type(): string
    {
        return MediaType::of($this->extension);
    }

    /** SVG kırpılamaz ve dönüştürülemez; olduğu gibi saklanır. */
    public function isCroppable(): bool
    {
        return $this->isImage() && $this->extension !== 'svg';
    }

    /** Diskte tuttuğu tüm dosyalar — silme sırasında kullanılır. */
    public function allPaths(): array
    {
        $video = $this->video ?? [];

        return array_values(array_filter([
            $this->path,
            $this->original_path,
            ...array_values($this->conversions ?? []),
            $video['poster']['path'] ?? null,
            $video['sprite']['path'] ?? null,
            $video['sprite']['vtt'] ?? null,
            ...array_column($video['renditions'] ?? [], 'path'),
        ]));
    }

    /**
     * Oynatıcının okuduğu tek gövde. Public JSON ve Blade config aynı şekil.
     * Video değilse null. Yükleyen / klasör / orijinal ad yok.
     *
     * @return array<string, mixed>|null
     */
    public function playerPayload(): ?array
    {
        if (! $this->isVideo()) {
            return null;
        }

        $video = $this->video ?? [];
        $status = $video['status'] ?? null;

        return [
            'src' => $this->url(),
            'poster' => isset($video['poster']['path']) ? $this->urlFor($video['poster']['path']) : null,
            'duration' => isset($video['duration']) ? (float) $video['duration'] : null,
            'status' => $status,
            'status_url' => $status === 'processing' ? route('media.player', $this) : null,
            'qualities' => $this->playerQualities($video),
            'sprite' => $this->playerSprite($video),
        ];
    }

    /** @param  array<string, mixed>  $video */
    private function playerQualities(array $video): array
    {
        if (($video['status'] ?? null) !== 'ready') {
            return [];
        }

        $qualities = [];
        $sourceHeight = (int) ($video['height'] ?? $this->height ?? 0);

        foreach ($video['renditions'] ?? [] as $label => $rendition) {
            $path = $rendition['path'] ?? null;

            if (! $path) {
                continue;
            }

            $qualities[] = [
                'id' => (string) $label,
                'label' => $label.'p',
                'src' => $this->urlFor($path),
            ];
        }

        $hasSameHeight = $sourceHeight > 0 && isset($video['renditions'][(string) $sourceHeight]);

        if ($sourceHeight > 0 && ! $hasSameHeight) {
            $qualities[] = [
                'id' => 'source',
                'label' => $sourceHeight.'p',
                'src' => $this->url(),
            ];
        }

        usort($qualities, function (array $a, array $b): int {
            $heightA = $a['id'] === 'source' ? (int) $a['label'] : (int) $a['id'];
            $heightB = $b['id'] === 'source' ? (int) $b['label'] : (int) $b['id'];

            return $heightB <=> $heightA;
        });

        return $qualities;
    }

    /** @param  array<string, mixed>  $video */
    private function playerSprite(array $video): ?array
    {
        $sprite = $video['sprite'] ?? null;
        $path = $sprite['path'] ?? null;
        $vtt = $sprite['vtt'] ?? null;

        if (! $path || ! $vtt) {
            return null;
        }

        return [
            'url' => $this->urlFor($path),
            'vtt' => $this->urlFor($vtt),
            'interval' => (int) ($sprite['interval'] ?? 2),
            'columns' => (int) ($sprite['columns'] ?? 5),
            'width' => (int) ($sprite['width'] ?? 160),
            'height' => (int) ($sprite['height'] ?? 90),
        ];
    }

    private function urlFor(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
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
            // Yeniden kırpma modalı bunu yükler — 'url' zaten kırpılmış sonuçtur.
            'original' => $this->originalUrl(),
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'human_size' => $this->humanSize(),
            'width' => $this->width,
            'height' => $this->height,
            'type' => $this->type(),
            'is_image' => $this->isImage(),
            'is_video' => $this->isVideo(),
            'is_croppable' => $this->isCroppable(),
            'preset' => $this->preset,
            'can_recrop' => (bool) $this->original_path,
            'folder_id' => $this->folder_id,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
            'video' => $this->playerPayload(),
        ];
    }

    public function humanSize(): string
    {
        return static::formatSize($this->size);
    }

    /** Sidebar depolama özeti gibi tekil bir kayda bağlı olmayan yerler için. */
    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = max($bytes, 0);
        $power = $size > 0 ? (int) floor(log($size, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return round($size / (1024 ** $power), $power > 1 ? 1 : 0).' '.$units[$power];
    }
}
