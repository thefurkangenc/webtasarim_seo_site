<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Dosya yükleme/silme tek noktadan yönetilir. Modüller kendi yükleme
 * mantığını yazmaz, bu servisi çağırır.
 *
 * Veritabanında saklanan değer daima diske göre göreli yoldur
 * (örn. "uploads/blog/2026/09/9f3c....webp"), tam URL değil.
 */
class MediaService
{
    private const DISK = 'public';

    /**
     * Dosyayı yükler ve saklanacak göreli yolu döndürür.
     *
     * @param  string  $folder  Modül klasörü, örn. "blog"
     */
    public function upload(UploadedFile $file, string $folder): string
    {
        $name = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs(
            "uploads/{$folder}/".now()->format('Y/m'),
            $name,
            self::DISK,
        );
    }

    /**
     * Yeni dosyayı yükler ve eskisini siler.
     *
     * @param  string|null  $current  Değiştirilecek mevcut yol
     */
    public function replace(UploadedFile $file, string $folder, ?string $current = null): string
    {
        $path = $this->upload($file, $folder);

        $this->delete($current);

        return $path;
    }

    /**
     * Dosyayı siler. Yol boşsa ya da dosya yoksa sessizce geçer.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * Saklanan göreli yolu görüntülenebilir URL'e çevirir.
     */
    public function url(?string $path): ?string
    {
        return $path ? Storage::disk(self::DISK)->url($path) : null;
    }
}
