<?php

namespace App\Services\Media;

use App\Models\Media\Media;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Direction;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Medya kütüphanesinin tek giriş noktası.
 *
 * Yükleme akışı: orijinal saklanır, kırpım koordinatları uygulanır, preset
 * boyutuna getirilir, WebP'ye çevrilir ve türetilmiş boyutlar üretilir.
 * Orijinal durduğu için kayıt sonradan yeniden kırpılabilir.
 *
 * SVG ve görsel olmayan dosyalar işlenmeden olduğu gibi saklanır.
 */
class MediaService
{
    public function __construct(private readonly MediaFolderService $folders) {}

    public function store(UploadedFile $file, array $options = []): Media
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $this->guard($file, $extension);

        $directory = config('media.directory').'/'.now()->format('Y/m');
        $uuid = (string) Str::uuid();
        $disk = config('media.disk');

        $attributes = $this->isProcessable($extension)
            ? $this->storeProcessed($file, $directory, $uuid, $options)
            : $this->storeRaw($file, $directory, $uuid, $extension);

        return Media::create([
            ...$attributes,
            'folder_id' => $options['folder_id'] ?? null,
            'disk' => $disk,
            'name' => $options['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'alt' => $options['alt'] ?? null,
            'title' => $options['title'] ?? null,
            'preset' => $options['preset'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Saklanan orijinalden yeni bir kırpım üretir. Ana dosya ve türetilmiş
     * boyutlar yenilenir; kaydın kimliği ve bağlantıları korunur.
     */
    public function recrop(Media $media, array $crop): Media
    {
        if (! $media->original_path || ! Storage::disk($media->disk)->exists($media->original_path)) {
            throw new DomainException('Bu görselin orijinali bulunamadığı için yeniden kırpılamıyor.');
        }

        $this->deleteFiles([$media->path, ...array_values($media->conversions ?? [])]);

        $directory = dirname($media->path);
        $uuid = pathinfo($media->path, PATHINFO_FILENAME);

        $image = $this->render(
            Storage::disk($media->disk)->path($media->original_path),
            $crop,
            $media->preset,
        );

        $path = "{$directory}/{$uuid}.webp";
        Storage::disk($media->disk)->put($path, (string) $image->encode($this->encoder()));

        $media->update([
            'path' => $path,
            'width' => $image->width(),
            'height' => $image->height(),
            'size' => Storage::disk($media->disk)->size($path),
            'crop' => $crop,
            'conversions' => $this->makeConversions($path, $directory, $uuid),
        ]);

        return $media;
    }

    public function update(Media $media, array $data): Media
    {
        $media->update(array_intersect_key($data, array_flip(['name', 'alt', 'title', 'folder_id'])));

        return $media;
    }

    public function delete(Media $media): void
    {
        $this->deleteFiles($media->allPaths(), $media->disk);

        $media->delete();
    }

    /** @param  array<int, int>  $ids */
    public function move(array $ids, ?int $folderId): void
    {
        Media::whereIn('id', $ids)->update(['folder_id' => $folderId]);
    }

    /** @param  array<int, int>  $ids */
    public function deleteMany(array $ids): int
    {
        $deleted = 0;

        foreach (Media::query()->whereIn('id', $ids)->get() as $media) {
            $this->delete($media);
            $deleted++;
        }

        return $deleted;
    }

    /**
     * Dosya yöneticisi grid'inin çoklu seçimde "Taşı" işlemi — dosyalar ve
     * klasörler aynı anda seçilebildiği için ikisini de tek çağrıda yürütür.
     *
     * @param  array<int, int>  $mediaIds
     * @param  array<int, int>  $folderIds
     * @return array{moved: int, skipped: array<int, array{name: string, reason: string}>}
     */
    public function bulkMove(array $mediaIds, array $folderIds, ?int $targetFolderId): array
    {
        $this->move($mediaIds, $targetFolderId);

        $result = $this->folders->moveMany($folderIds, $targetFolderId);
        $result['moved'] += count($mediaIds);

        return $result;
    }

    /**
     * Çoklu seçimde "Sil" — dolu klasörler güvenlik kuralı gereği atlanır,
     * dosyalar koşulsuz silinir (kalıcı, diskten de).
     *
     * @param  array<int, int>  $mediaIds
     * @param  array<int, int>  $folderIds
     * @return array{deleted: int, skipped: array<int, array{name: string, reason: string}>}
     */
    public function bulkDelete(array $mediaIds, array $folderIds): array
    {
        $deleted = $this->deleteMany($mediaIds);

        $result = $this->folders->deleteMany($folderIds);
        $result['deleted'] += $deleted;

        return $result;
    }

    /** Sidebar'daki depolama özeti. */
    public function stats(): array
    {
        $count = Media::query()->count();
        $size = (int) Media::query()->sum('size');

        return ['count' => $count, 'size' => $size, 'human_size' => Media::formatSize($size)];
    }

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Media::query()->with('folder:id,name');

        $this->applyFilters($query, $filters);

        return $query
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 30)
            ->through(fn (Media $media) => $media->toPayload());
    }

    /**
     * Kırpma/ölçekleme zinciri. Sıra Cropper.js'in modeliyle aynı olmalıdır:
     * önce döndürme, sonra aynalama, sonra kırpma.
     */
    private function render(string $sourcePath, ?array $crop, ?string $preset): ImageInterface
    {
        $image = $this->manager()->decodePath($sourcePath);

        if ($crop) {
            if (! empty($crop['rotate'])) {
                // Her ikisi de saat yönünde döndürür; açı olduğu gibi geçirilir.
                // (Piksel testiyle doğrulandı: 90 -> sol-üst köşe sağ-üste gider.)
                $image->rotate((float) $crop['rotate']);
            }

            if (($crop['scaleX'] ?? 1) < 0) {
                $image->flip(Direction::HORIZONTAL);
            }

            if (($crop['scaleY'] ?? 1) < 0) {
                $image->flip(Direction::VERTICAL);
            }

            if (isset($crop['width'], $crop['height'])) {
                $image->crop(
                    max(1, (int) round($crop['width'])),
                    max(1, (int) round($crop['height'])),
                    (int) round($crop['x'] ?? 0),
                    (int) round($crop['y'] ?? 0),
                );
            }
        }

        // Dizi erişimi bilinçli: preset anahtarları nokta içerir ('blog.cover'),
        // config() bunu iç içe dizi sanıp bulamaz.
        $size = $preset ? (config('media.presets', [])[$preset] ?? null) : null;

        if ($size) {
            // cover, çıktının tam olarak preset boyutunda olmasını garanti eder.
            $image->cover($size['width'], $size['height']);
        }

        return $image;
    }

    /** @return array<string, mixed> */
    private function storeProcessed(UploadedFile $file, string $directory, string $uuid, array $options): array
    {
        $disk = config('media.disk');
        $extension = strtolower($file->getClientOriginalExtension());

        // Orijinal taşınmadan önce işlenmeli; storeAs geçici dosyayı taşır.
        $image = $this->render($file->getPathname(), $options['crop'] ?? null, $options['preset'] ?? null);
        $binary = (string) $image->encode($this->encoder());

        $originalPath = $file->storeAs($directory, "{$uuid}-original.{$extension}", $disk);
        $path = "{$directory}/{$uuid}.webp";
        Storage::disk($disk)->put($path, $binary);

        return [
            'path' => $path,
            'original_path' => $originalPath,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => strlen($binary),
            'width' => $image->width(),
            'height' => $image->height(),
            'crop' => $options['crop'] ?? null,
            'conversions' => $this->makeConversions($path, $directory, $uuid),
        ];
    }

    /** @return array<string, mixed> */
    private function storeRaw(UploadedFile $file, string $directory, string $uuid, string $extension): array
    {
        $disk = config('media.disk');
        $contents = $extension === 'svg'
            ? $this->sanitizeSvg(file_get_contents($file->getPathname()))
            : file_get_contents($file->getPathname());

        $path = "{$directory}/{$uuid}.{$extension}";
        Storage::disk($disk)->put($path, $contents);

        return [
            'path' => $path,
            'original_path' => null,
            'mime_type' => $file->getClientMimeType(),
            'extension' => $extension,
            'size' => strlen($contents),
            'width' => null,
            'height' => null,
            'crop' => null,
            'conversions' => null,
        ];
    }

    /** @return array<string, string> */
    private function makeConversions(string $mainPath, string $directory, string $uuid): array
    {
        $disk = config('media.disk');
        $absolute = Storage::disk($disk)->path($mainPath);
        $conversions = [];

        foreach (config('media.conversions', []) as $name => $spec) {
            $image = $this->manager()->decodePath($absolute);

            ($spec['fit'] ?? 'scale') === 'cover'
                ? $image->cover($spec['width'], $spec['height'])
                : $image->scaleDown(width: $spec['width'], height: $spec['height']);

            $path = "{$directory}/{$uuid}-{$name}.webp";
            Storage::disk($disk)->put($path, (string) $image->encode($this->encoder()));

            $conversions[$name] = $path;
        }

        return $conversions;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when(
                array_key_exists('folder_id', $filters) && ! ($filters['unattached'] ?? false),
                fn (Builder $q) => $q->where('folder_id', $filters['folder_id'] ?: null),
            )
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(
                fn (Builder $q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%")
            ))
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => match ($type) {
                'image' => $q->where('mime_type', 'like', 'image/%'),
                'other' => $q->where('mime_type', 'not like', 'image/%'),
                default => $q,
            })
            // Hiçbir kayda bağlanmamış dosyalar — terk edilmiş formlardan kalanlar.
            ->when($filters['unattached'] ?? false, fn (Builder $q) => $q->whereNotExists(
                fn ($sub) => $sub->select(DB::raw(1))
                    ->from('mediables')
                    ->whereColumn('mediables.media_id', 'media.id')
            ));
    }

    private function guard(UploadedFile $file, string $extension): void
    {
        if (! in_array($extension, config('media.accepts', []), true)) {
            throw new DomainException("'{$extension}' uzantılı dosyalar yüklenemez.");
        }

        if ($file->getSize() > config('media.max_size') * 1024) {
            throw new DomainException('Dosya boyutu '.config('media.max_size') / 1024 .' MB sınırını aşıyor.');
        }
    }

    private function isProcessable(string $extension): bool
    {
        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    /**
     * SVG'de gömülü script ve olay işleyicilerini temizler.
     * Kapsamlı bir sanitizer değildir; SVG yüklemesini yalnızca güvenilen
     * kullanıcılara açık tutun.
     */
    private function sanitizeSvg(string $contents): string
    {
        $contents = preg_replace('#<script[^>]*>.*?</script>#is', '', $contents) ?? $contents;
        $contents = preg_replace('#<(script|foreignObject|iframe|embed|object)\b[^>]*/?>#i', '', $contents) ?? $contents;
        $contents = preg_replace('#\son[a-z]+\s*=\s*(["\']).*?\1#is', '', $contents) ?? $contents;
        $contents = preg_replace('#(href|xlink:href)\s*=\s*(["\'])\s*javascript:.*?\2#is', '', $contents) ?? $contents;

        return $contents;
    }

    /** @param  array<int, string>  $paths */
    private function deleteFiles(array $paths, ?string $disk = null): void
    {
        $disk ??= config('media.disk');

        foreach (array_filter($paths) as $path) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    private function manager(): ImageManager
    {
        return ImageManager::usingDriver(
            config('media.driver') === 'imagick' ? new ImagickDriver : new GdDriver,
        );
    }

    private function encoder(): WebpEncoder
    {
        return new WebpEncoder(quality: config('media.quality', 85));
    }
}
