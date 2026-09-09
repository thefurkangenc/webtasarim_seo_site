<?php

namespace App\Models\Service;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\HasTags;
use App\Models\ServiceRegion\ServiceRegion;
use App\Models\User;
use App\Support\Placeholder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Hizmet. İçerik bir kez yazılır, bağlı olduğu her hizmet bölgesi için
 * yer tutucular çözülerek yeniden üretilir (bkz. renderFor()).
 */
#[Fillable(['user_id', 'title', 'slug', 'excerpt', 'content', 'status', 'sort_order'])]
class Service extends Model
{
    use HasMedia, HasSeo, HasSortOrder, HasTags;

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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Pivot tablo adı açıkça verilir; Eloquent'in türeteceği ad yanlış olur. */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(ServiceRegion::class, 'service_region_service');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'regions_count' => $this->regions_count ?? 0,
            'author' => $this->author?->name,
            'thumb' => $this->mediaUrl('cover', 'thumb'),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }

    /**
     * İçeriği verilen bölge için çözülmüş halde döndürür: metinlerdeki
     * {{region}} / {{city}} / {{district}} yer tutucuları o bölgenin
     * değerleriyle değişir. Ön yüz her (hizmet, bölge) çifti için bunu kullanır.
     *
     * @return array{title: string, excerpt: string|null, content: string|null, seo: array<string, mixed>}
     */
    public function renderFor(ServiceRegion $region): array
    {
        $values = $region->placeholders();

        return [
            'title' => Placeholder::replace($this->title, $values),
            'excerpt' => Placeholder::replace($this->excerpt, $values),
            'content' => Placeholder::replace($this->content, $values),
            'seo' => Placeholder::replaceAll($this->seoMeta(), $values),
        ];
    }
}
