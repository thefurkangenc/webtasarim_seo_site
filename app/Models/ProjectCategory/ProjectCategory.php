<?php

namespace App\Models\ProjectCategory;

use App\Contracts\RedirectsOnMove;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use App\Models\Project\Project;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'sort_order', 'is_active'])]
class ProjectCategory extends Model implements RedirectsOnMove
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

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Kategori sayfaları (/projeler/kategori/{slug}) indekslenebilir olduğu
     * için slug değişimi ölü URL bırakmamalı.
     *
     * @return array{from: string, to: string}|null
     */
    public function redirectableMove(): ?array
    {
        if (! $this->wasChanged('slug')) {
            return null;
        }

        return [
            'from' => 'projeler/kategori/'.$this->getOriginal('slug'),
            'to' => 'projeler/kategori/'.$this->slug,
        ];
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'projects_count' => $this->projects_count ?? 0,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
