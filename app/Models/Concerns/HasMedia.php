<?php

namespace App\Models\Concerns;

use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Modele medya bağlama yeteneği verir.
 *
 * Koleksiyon, aynı model üzerindeki farklı görsel alanlarını ayırır:
 * 'cover' tek görsel, 'gallery' çoklu — mekanizma aynıdır.
 *
 *   $blog->getFirstMedia('cover');
 *   $blog->mediaUrl('cover', 'thumb');
 *   $blog->syncMedia($request->input('cover_media_id'), 'cover');
 */
trait HasMedia
{
    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable')
            ->withPivot(['collection', 'sort_order', 'is_cover'])
            ->orderBy('mediables.sort_order');
    }

    /** @return Collection<int, Media> */
    public function getMedia(string $collection = 'default'): Collection
    {
        return $this->media->where('pivot.collection', $collection)->values();
    }

    public function getFirstMedia(string $collection = 'default'): ?Media
    {
        return $this->getMedia($collection)->first();
    }

    /**
     * Galeri koleksiyonlarında kapak olarak işaretlenen görsel.
     * İşaretli yoksa sıradaki ilk görsele düşer.
     */
    public function getCoverMedia(string $collection = 'default'): ?Media
    {
        $media = $this->getMedia($collection);

        return $media->firstWhere('pivot.is_cover', true) ?? $media->first();
    }

    public function mediaUrl(string $collection = 'default', ?string $conversion = null): ?string
    {
        return $this->getFirstMedia($collection)?->url($conversion);
    }

    /**
     * Koleksiyonun içeriğini verilen medya ile değiştirir.
     * null ya da boş dizi koleksiyonu temizler.
     *
     * `$coverId` yalnızca çoklu (galeri) alanlarda anlamlıdır: verilen id
     * listede yoksa yok sayılır, hiç verilmezse kapak işareti konmaz ve
     * `getCoverMedia()` sıradaki ilk görsele düşer.
     *
     * @param  int|array<int, int>|null  $mediaIds
     */
    public function syncMedia(int|array|null $mediaIds, string $collection = 'default', ?int $coverId = null): void
    {
        $ids = array_values(array_filter((array) $mediaIds));

        $this->media()->wherePivot('collection', $collection)->detach();

        if ($ids === []) {
            $this->unsetRelation('media');

            return;
        }

        $this->media()->attach(
            collect($ids)->mapWithKeys(fn (int $id, int $index) => [
                $id => [
                    'collection' => $collection,
                    'sort_order' => $index,
                    'is_cover' => $id === $coverId,
                ],
            ])->all(),
        );

        $this->unsetRelation('media');
    }
}
