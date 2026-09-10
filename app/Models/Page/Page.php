<?php

namespace App\Models\Page;

use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Panelden yönetilen statik sayfa.
 *
 * Hiyerarşi `parent_id` ile kurulur, fakat ön yüz hiçbir zaman ağacı
 * gezmez: çözülmüş tam yol `path` kolonunda durur ("kurumsal/hakkimizda")
 * ve tek sorguyla okunur. `path`'i yazan tek yer PageService'tir — üst sayfa
 * değişince alt ağacın tamamı yeniden yazılır.
 *
 * `ORDER BY path` doğal ağaç sırası verir ("a", "a/b", "a/c", "b"), bu yüzden
 * liste ekranında girintili görünüm için ek bir sorgu gerekmez.
 */
#[Fillable(['parent_id', 'user_id', 'title', 'slug', 'path', 'excerpt', 'content', 'template', 'status', 'sort_order', 'published_at'])]
class Page extends Model
{
    use HasFaqs, HasMedia, HasSeo, HasSortOrder, HasTags, LogsActivity;

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
            'published_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Yayında olan ve yayın tarihi gelmiş sayfalar. `published_at` ileri bir
     * tarihse sayfa panelde "Yayında" görünür ama ön yüzde henüz çıkmaz —
     * zamanlanmış yayın bu tek kapsamla elde edilir.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /** Sıralama kardeşler arasında; her üst sayfanın altı kendi içinde sıralanır. */
    protected function sortOrderScope(Builder $query): Builder
    {
        return $query->where('parent_id', $this->parent_id);
    }

    /** Kökte 0, /a/b'de 1, /a/b/c'de 2. */
    public function depth(): int
    {
        return substr_count((string) $this->path, '/');
    }

    public function url(): string
    {
        return url($this->path);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** @return array<string, mixed> */
    public function templateMeta(): array
    {
        return config("pages.templates.{$this->template}")
            ?? ['label' => $this->template, 'description' => null, 'view' => 'pages.page.default'];
    }

    public function isVisible(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && ($this->published_at === null || $this->published_at->lessThanOrEqualTo(Carbon::now()));
    }

    /**
     * Breadcrumb için üst sayfaların yolları, kökten bu sayfanın bir üstüne
     * kadar: "a/b/c" -> ["a", "a/b"]. Ağacı gezmeden tek `whereIn` sorgusuna
     * dönüştürülebildiği için PageService bunu kullanır.
     *
     * @return array<int, string>
     */
    public function ancestorPaths(): array
    {
        $segments = explode('/', (string) $this->path);
        array_pop($segments);

        $paths = [];
        $current = '';

        foreach ($segments as $segment) {
            $current = $current === '' ? $segment : "{$current}/{$segment}";
            $paths[] = $current;
        }

        return $paths;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'path' => $this->path,
            'url' => $this->url(),
            'depth' => $this->depth(),
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'is_visible' => $this->isVisible(),
            'template' => $this->template,
            'template_label' => $this->templateMeta()['label'],
            'children_count' => $this->children_count ?? 0,
            'author' => $this->author?->name,
            'sort_order' => $this->sort_order,
            'published_at' => $this->published_at?->format('d.m.Y H:i'),
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
