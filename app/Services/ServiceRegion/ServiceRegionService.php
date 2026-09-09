<?php

namespace App\Services\ServiceRegion;

use App\Models\ServiceRegion\ServiceRegion;
use App\Services\Concerns\ReordersRecords;
use App\Support\Slug;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Hizmet bölgesi ağacı. Liste kırılımlı çalışır: normalde yalnızca bir
 * seviyenin kayıtları döner, arama yapıldığında ağacın tamamında aranır.
 *
 * `path` ve `depth` türetilmiş alanlardır — burada hesaplanır, formdan
 * gelmez. Bir kayıt yeniden adlandırıldığında altındaki tüm ağacın `path`i
 * de tazelenir.
 */
class ServiceRegionService
{
    use ReordersRecords;

    /** Bir kaydın altındaki ağaç bu derinliği aşarsa döngü vardır. */
    private const MAX_DEPTH = 10;

    public function list(array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return ServiceRegion::query()
            ->withCount(['children', 'services'])
            // Arama ağacın tamamında yapılır; kırılım seviyesi göz ardı edilir,
            // sonuçta tam yol (`path`) gösterilir.
            ->when($search, fn ($query, $term) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('path', 'like', "%{$term}%"),
            ))
            ->unless($search, fn ($query) => $query->where('parent_id', $filters['parent_id'] ?? null))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '',
                fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (ServiceRegion $region) => $region->toPayload());
    }

    /**
     * Kırılım başlığı: kökten verilen bölgeye kadarki zincir.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function breadcrumb(ServiceRegion $region): array
    {
        return $region->ancestorsAndSelf()
            ->map(fn (ServiceRegion $item) => ['id' => $item->id, 'name' => $item->name])
            ->all();
    }

    public function create(array $data): ServiceRegion
    {
        $parent = $this->parent($data['parent_id'] ?? null);

        return ServiceRegion::create([
            ...$this->attributes($data),
            'parent_id' => $parent?->id,
            'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['name'], 'service_regions'),
            ...$this->derive($parent, $data['name']),
        ]);
    }

    /**
     * Üst bölge formda yer almaz — bölge bulunduğu seviyede kalır. Ad
     * değiştiğinde `path` hem kendisinde hem alt ağacında tazelenir.
     */
    public function update(ServiceRegion $region, array $data): ServiceRegion
    {
        return DB::transaction(function () use ($region, $data) {
            $region->update([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['name'], 'service_regions', $region->id),
                ...$this->derive($region->parent, $data['name']),
            ]);

            $this->refreshSubtree($region);

            return $region;
        });
    }

    /**
     * Dolu bölge silinmez. Alt bölgeler DB'de cascade ile bağlıdır ama
     * sessizce yüzlerce kaydı yok etmek yerine kullanıcıyı uyarıyoruz.
     */
    public function delete(ServiceRegion $region): void
    {
        if ($region->children()->exists()) {
            throw new DomainException('Alt bölgesi olan bir bölge silinemez. Önce alt bölgeleri silin.');
        }

        if ($region->services()->exists()) {
            throw new DomainException('Bu bölgeye bağlı hizmetler var. Önce hizmetlerden bu bölgeyi kaldırın.');
        }

        $region->delete();
    }

    /** @return array<string, mixed> */
    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    /** Üst bölgeye göre türetilen alanlar. */
    private function derive(?ServiceRegion $parent, string $name): array
    {
        return [
            'depth' => $parent ? $parent->depth + 1 : 0,
            'path' => $parent ? "{$parent->path} {$name}" : $name,
        ];
    }

    private function parent(?int $parentId): ?ServiceRegion
    {
        return $parentId ? ServiceRegion::findOrFail($parentId) : null;
    }

    /** Ad değişince altındaki tüm yolları yeniden yazar. */
    private function refreshSubtree(ServiceRegion $region, int $level = 0): void
    {
        if ($level >= self::MAX_DEPTH) {
            throw new DomainException('Bölge ağacı beklenenden derin — kayıtları kontrol edin.');
        }

        foreach ($region->children()->get() as $child) {
            $child->update($this->derive($region, $child->name));

            $this->refreshSubtree($child, $level + 1);
        }
    }

    protected function reorderModel(): string
    {
        return ServiceRegion::class;
    }
}
