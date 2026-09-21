<?php

namespace App\Models\BlogCategory;

use App\Contracts\LinksToPublicPage;
use App\Contracts\RedirectsOnMove;
use App\Models\Blog\Blog;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'sort_order', 'is_active'])]
class BlogCategory extends Model implements LinksToPublicPage, RedirectsOnMove
{
    use HasSeo, HasSortOrder, LogsActivity;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    /** Pasif kategorinin ön yüzde adresi yoktur — menü öğesi kendiliğinden düşer. */
    public function publicUrl(): ?string
    {
        return $this->is_active ? route('blog.kategori', $this->slug) : null;
    }

    public function publicLinkLabel(): string
    {
        return $this->name;
    }

    /**
     * Kategori sayfaları (/blog/kategori/{slug}) indekslenebilir olduğu için
     * slug değişimi ölü URL bırakmamalı.
     *
     * @return array{from: string, to: string}|null
     */
    public function redirectableMove(): ?array
    {
        if (! $this->wasChanged('slug')) {
            return null;
        }

        return [
            'from' => 'blog/kategori/'.$this->getOriginal('slug'),
            'to' => 'blog/kategori/'.$this->slug,
        ];
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'blogs_count' => $this->blogs_count ?? 0,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
