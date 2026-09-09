<?php

namespace App\Models\ServiceRegion;

use App\Models\Concerns\HasSortOrder;
use App\Models\Service\Service;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Hizmet bölgesi — sonsuz derinlikli ağaç. parent_id boşsa şehir (depth 0),
 * doluysa alt bölge. Şehirlerin id'si plaka kodudur, ServiceRegionSeeder basar.
 */
#[Fillable(['parent_id', 'name', 'slug', 'path', 'depth', 'description', 'sort_order', 'is_active'])]
class ServiceRegion extends Model
{
    use HasSortOrder;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_region_service');
    }

    /** Sıralama tüm tabloda değil, bulunduğu seviyede tekil. */
    protected function sortOrderScope(Builder $query): Builder
    {
        return $query->where('parent_id', $this->parent_id);
    }

    /**
     * Kökten bu kayda kadarki zincir. En fazla `depth` kez yukarı çıkar —
     * ağaç üç-dört seviye olduğu için ilişkiyi tembel yüklemek yeterli.
     *
     * @return Collection<int, self>
     */
    public function ancestorsAndSelf(): Collection
    {
        $chain = collect([$this]);

        for ($node = $this; $node = $node->parent;) {
            $chain->prepend($node);
        }

        return $chain;
    }

    /**
     * İçerikteki yer tutucuların bu bölge için değerleri.
     * Gaziantep › Şahinbey için: region="Gaziantep - Şahinbey",
     * city="Gaziantep", district="Şahinbey".
     *
     * @return array<string, string>
     */
    public function placeholders(): array
    {
        $names = $this->ancestorsAndSelf()->pluck('name');

        return [
            'region' => $names->implode(' - '),
            'city' => (string) $names->first(),
            'district' => (string) ($names->get(1) ?? ''),
        ];
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'path' => $this->path,
            'depth' => $this->depth,
            'is_active' => $this->is_active,
            'children_count' => $this->children_count ?? 0,
            'services_count' => $this->services_count ?? 0,
            'has_description' => filled($this->description),
        ];
    }
}
