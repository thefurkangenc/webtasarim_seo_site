<?php

namespace App\Models\Blog;

use App\Models\BlogCategory\BlogCategory;
use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blog_category_id', 'user_id', 'title', 'slug', 'excerpt', 'content',
    'status', 'published_at', 'is_featured',
])]
class Blog extends Model
{
    use HasFaqs, HasMedia, HasSeo, HasTags, LogsActivity;

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
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
            'is_featured' => $this->is_featured,
            'category' => $this->category?->name,
            'author' => $this->author?->name,
            'thumb' => $this->mediaUrl('cover', 'thumb'),
            'published_at' => $this->published_at?->format('d.m.Y H:i'),
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
