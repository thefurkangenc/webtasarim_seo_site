<?php

namespace App\Services\Hero;

use App\Models\Hero\Hero;
use Illuminate\Support\Facades\DB;

/**
 * Tanıtım alanı tekil bir kayıttır: liste, ekleme ve silme yoktur.
 * Ön yüz de bu servisi kullanır (Admin/ segmenti yok).
 */
class HeroService
{
    /** Tek satır; henüz yoksa boş olarak oluşturulur. */
    public function current(): Hero
    {
        return Hero::firstOrCreate([]);
    }

    public function update(array $data): Hero
    {
        return DB::transaction(function () use ($data) {
            $hero = $this->current();

            $hero->update([
                'badge' => $data['badge'] ?? null,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'button_url' => $data['button_url'] ?? null,
            ]);

            $hero->syncMedia($data['background_media_id'] ?? null, 'background');
            $hero->syncMedia(
                $data['gallery_media_ids'] ?? [],
                'gallery',
                $data['gallery_media_ids_cover'] ?? null,
            );

            return $hero;
        });
    }
}
