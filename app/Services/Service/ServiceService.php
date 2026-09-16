<?php

namespace App\Services\Service;

use App\Models\Service\Service;
use App\Models\ServiceRegion\ServiceRegion;
use App\Services\Concerns\ReordersRecords;
use App\Support\Placeholder;
use App\Support\Slug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return Service::query()
            ->with(['author:id,name', 'media', 'seo'])
            ->with('regions:id,slug_path,is_active')
            ->withCount('regions')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('excerpt', 'like', "%{$term}%"),
            ))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            // Bölge filtresi pivot üzerinden: yalnızca o ile bağlı hizmetler (filtrede yalnızca iller var).
            ->when($filters['service_region_id'] ?? null,
                fn ($query, $id) => $query->whereHas('regions', fn ($q) => $q->whereKey($id)))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Service $service) => $service->toPayload());
    }

    public function create(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            $service = Service::create([
                ...$this->attributes($data),
                'slug' => Slug::unique($this->slugSource($data), 'services'),
                'user_id' => auth()->id(),
            ]);

            $this->syncRelations($service, $data);

            return $service;
        });
    }

    public function update(Service $service, array $data): Service
    {
        return DB::transaction(function () use ($service, $data) {
            $service->update([
                ...$this->attributes($data),
                'slug' => Slug::unique($this->slugSource($data), 'services', $service->id),
            ]);

            $this->syncRelations($service, $data);

            return $service;
        });
    }

    public function delete(Service $service): void
    {
        DB::transaction(function () use ($service) {
            // Medya kütüphanedeki dosyayı silmez, yalnızca bağı koparır.
            $service->syncMedia(null, 'cover');
            $service->tags()->detach();
            $service->faqs()->detach();
            $service->regions()->detach();
            $service->seo()->delete();
            $service->delete();
        });
    }

    /**
     * Ön yüzde yayındaki hizmetler, sıraya göre — ana sayfa teaser'ı ve
     * /hizmetler listesi bunu kullanır.
     *
     * @return Collection<int, Service>
     */
    public function active(?int $limit = null): Collection
    {
        return Service::where('status', Service::STATUS_PUBLISHED)
            ->with('media')
            ->orderBy('sort_order')
            ->when($limit, fn ($query, $limit) => $query->limit($limit))
            ->get();
    }

    /**
     * Ön yüzde slug ile tekil hizmet — bağlı olduğu aktif bölgelerle birlikte
     * (/hizmetler/{hizmet}/{bölge} sayfası ve bölge kenar çubuğu bunu okur).
     * Taslak bir hizmet ya da hiç eşleşmeyen slug için null döner.
     */
    public function findBySlug(string $slug): ?Service
    {
        return Service::where('slug', $slug)
            ->where('status', Service::STATUS_PUBLISHED)
            ->with([
                'media',
                'seo.ogMedia',
                'faqs',
                'regions' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
            ])
            ->first();
    }

    /**
     * Adres yolundan bölgeyi çözer: "gaziantep/sahinbey" -> Şahinbey kaydı.
     * Yalnızca hizmetin kapsadığı aktif bölgeler eşleşir (seçili il + altı,
     * bkz. Service::coveredRegions()); uydurma bir yol null döner.
     */
    public function findRegion(Service $service, string $path): ?ServiceRegion
    {
        return $service->coveredRegions()->firstWhere('slug_path', $path);
    }

    /**
     * Kenar çubuğundaki bölge listesi: her il kendi altındaki bölgelerle bir
     * grup olur. Formda yalnızca il seçildiği için grubun başı her zaman
     * tıklanabilir bir sayfadır; alt bölgeler ağaç sırasıyla gelir.
     *
     * @return list<array{city: ServiceRegion, page: ServiceRegion|null, children: Collection<int, ServiceRegion>}>
     */
    public function regionGroups(Service $service): array
    {
        return $service->coveredRegions()
            ->groupBy(fn (ServiceRegion $region) => Str::before($region->slug_path, '/'))
            ->map(fn (Collection $regions) => [
                'city' => $regions->first(),
                'page' => $regions->first(),
                'children' => $regions->where('depth', '>', 0)->values(),
            ])
            ->values()
            ->all();
    }

    protected function reorderModel(): string
    {
        return Service::class;
    }

    /**
     * Slug bölgeden bağımsızdır — URL'de bölge ayrı bir segment olarak durur.
     * Bu yüzden başlıktaki yer tutucular slug'a girmeden atılır:
     * "{{city}} Web Tasarım" -> "web-tasarim". Elle slug girildiyse aynen kalır.
     */
    private function slugSource(array $data): string
    {
        return ($data['slug'] ?? null) ?: (Placeholder::strip($data['title']) ?: $data['title']);
    }

    /** `sort_order` burada yok: formda girilmez, HasSortOrder verir, sıralama modu değiştirir. */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'status' => $data['status'] ?? Service::STATUS_DRAFT,
        ];
    }

    /** Paylaşılan bileşenlerin kaydı: kapak görseli, etiketler, SEO, FAQ ve bölgeler. */
    private function syncRelations(Service $service, array $data): void
    {
        $service->syncMedia($data['cover_media_id'] ?? null, 'cover');
        $service->syncTags($data['tags'] ?? []);
        $service->syncSeo($data['seo'] ?? []);
        $service->syncFaqs($data['faqs'] ?? []);
        $service->regions()->sync($data['service_regions'] ?? []);
    }
}
