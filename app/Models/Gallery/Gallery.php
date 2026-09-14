<?php

namespace App\Models\Gallery;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasRevisions;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Foto galeri kaydı. Her satır bir albümdür; fotoğraflar mediables pivotu
 * üzerinden `gallery` koleksiyonunda durur, tabloda görsel kolonu yoktur.
 */
#[Fillable(['title', 'slug', 'description', 'status', 'sort_order'])]
class Gallery extends Model
{
    use HasMedia, HasRevisions, HasSeo, HasSortOrder, LogsActivity {
        HasSeo::seoAnalysisInput as baseSeoAnalysisInput;
        HasRevisions::revisionRestorePayload as baseRevisionRestorePayload;
    }

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    /** Durum seçeneklerinin tek kaynağı — form select'i ve doğrulama buradan okur. */
    public const STATUSES = [
        self::STATUS_DRAFT => 'Taslak',
        self::STATUS_PUBLISHED => 'Yayında',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Geri yükleme payload'ı formun beklediği `gallery_media_ids` adına
     * çevrilir. Trait tekil koleksiyonları `*_media_id` üretir; çoklu alan
     * dizi + kapak ister.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public function revisionRestorePayload(array $snapshot): array
    {
        $payload = $this->baseRevisionRestorePayload($snapshot);
        $ids = array_values(array_filter((array) ($payload['gallery_media_id'] ?? [])));

        unset($payload['gallery_media_id']);

        $payload['gallery_media_ids'] = $ids;
        $payload['gallery_media_ids_cover'] = $snapshot['media']['gallery']['cover_id'] ?? null;

        return $payload;
    }

    /**
     * Bu modülde ayrı kapak alanı yok: paylaşım görseli ve liste küçük resmi
     * galeride kapak işaretli (yoksa ilk) fotoğraftan okunur.
     *
     * @return array<string, string|null>
     */
    protected function seoFallbacks(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->getCoverMedia('gallery')?->url(),
        ];
    }

    /**
     * Zengin metin yok; analiz açıklama alanını içerik olarak okur ki
     * kelime sayısı sıfır görünmesin.
     *
     * @return array<string, string>
     */
    public function seoAnalysisInput(): array
    {
        $input = $this->baseSeoAnalysisInput();
        $input['content'] = (string) $this->description;

        return $input;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            ...$this->seoScorePayload(),
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'photos_count' => $this->getMedia('gallery')->count(),
            'thumb' => $this->getCoverMedia('gallery')?->url('thumb'),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
