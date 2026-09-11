<?php

namespace App\Services\Project;

use App\Models\Project\Project;
use App\Models\ProjectCategory\ProjectCategory;
use App\Models\Service\Service;
use App\Models\Testimonial\Testimonial;
use App\Services\Concerns\ReordersRecords;
use App\Support\Slug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return Project::query()
            ->with(['category:id,name', 'author:id,name', 'media', 'seo'])
            ->withCount('services')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%")
                    ->orWhere('client_name', 'like', "%{$term}%")
                    ->orWhere('sector', 'like', "%{$term}%"),
            ))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['project_category_id'] ?? null,
                fn ($query, $id) => $query->where('project_category_id', $id))
            // Hizmet filtresi pivot üzerinden: yalnızca o hizmete bağlı projeler.
            ->when($filters['service_id'] ?? null,
                fn ($query, $id) => $query->whereHas('services', fn ($q) => $q->whereKey($id)))
            ->when(! empty($filters['featured']), fn ($query) => $query->where('is_featured', true))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Project $project) => $project->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?Project $project): array
    {
        $project?->load(['media', 'tags', 'faqs', 'seo', 'services:id']);

        return [
            'project' => $project,
            'categories' => ProjectCategory::where('is_active', true)->orderBy('sort_order')->pluck('name', 'id')->all(),
            'services' => Service::orderBy('sort_order')->pluck('title', 'id')->all(),
            'testimonials' => Testimonial::where('is_active', true)->orderBy('sort_order')
                // Yorumun kendisi uzun; seçim listesinde kişi adı + kurum yeter.
                ->get(['id', 'name', 'title'])
                ->mapWithKeys(fn (Testimonial $item) => [
                    $item->id => filled($item->title) ? "{$item->name} — {$item->title}" : $item->name,
                ])
                ->all(),
        ];
    }

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['title'], 'projects'),
                'user_id' => auth()->id(),
            ]);

            $this->syncRelations($project, $data);

            return $project;
        });
    }

    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['title'], 'projects', $project->id),
            ]);

            $this->syncRelations($project, $data);

            return $project;
        });
    }

    public function delete(Project $project): void
    {
        DB::transaction(function () use ($project) {
            // Medya kütüphanedeki dosyaları silmez, yalnızca bağı koparır.
            $project->syncMedia(null, 'cover');
            $project->syncMedia([], 'gallery');
            $project->syncMedia(null, 'video');
            $project->tags()->detach();
            $project->faqs()->detach();
            $project->services()->detach();
            $project->seo()->delete();
            $project->delete();
        });
    }

    /**
     * Ön yüzde yayındaki projeler, sıraya göre. Kategori ya da hizmet verilirse
     * o kırılımla daraltılır — "bu hizmette yaptığımız işler" bloğu bunu kullanır.
     *
     * @return Collection<int, Project>
     */
    public function active(?int $limit = null, ?int $categoryId = null, ?int $serviceId = null): Collection
    {
        return Project::where('status', Project::STATUS_PUBLISHED)
            ->with(['media', 'category:id,name,slug'])
            ->when($categoryId, fn ($query, $id) => $query->where('project_category_id', $id))
            ->when($serviceId, fn ($query, $id) => $query->whereHas('services', fn ($q) => $q->whereKey($id)))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->when($limit, fn ($query, $limit) => $query->limit($limit))
            ->get();
    }

    /** Ön yüzde slug ile tekil proje. Taslak ya da eşleşmeyen slug için null. */
    public function findBySlug(string $slug): ?Project
    {
        return Project::where('slug', $slug)
            ->where('status', Project::STATUS_PUBLISHED)
            ->with(['media', 'seo.ogMedia', 'faqs', 'tags', 'category:id,name,slug', 'testimonial', 'services'])
            ->first();
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        return [
            'total' => Project::count(),
            'published' => Project::where('status', Project::STATUS_PUBLISHED)->count(),
            'draft' => Project::where('status', Project::STATUS_DRAFT)->count(),
            'featured' => Project::where('is_featured', true)->count(),
        ];
    }

    protected function reorderModel(): string
    {
        return Project::class;
    }

    /** `sort_order` burada yok: formda girilmez, HasSortOrder verir, sıralama modu değiştirir. */
    private function attributes(array $data): array
    {
        return [
            'project_category_id' => $data['project_category_id'] ?? null,
            'testimonial_id' => $data['testimonial_id'] ?? null,
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'client_name' => $data['client_name'] ?? null,
            'sector' => $data['sector'] ?? null,
            'project_url' => $data['project_url'] ?? null,
            'started_at' => $data['started_at'] ?? null,
            'completed_at' => $data['completed_at'] ?? null,
            'duration' => $data['duration'] ?? null,
            'technologies' => $this->technologies($data),
            'results' => $this->results($data),
            'video_url' => $data['video_url'] ?? null,
            'status' => $data['status'] ?? Project::STATUS_DRAFT,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
        ];
    }

    /**
     * Teknoloji listesi JSON dizisi olarak gelir (form chips alanı). Boş
     * girişler atılır, tekrarlar teklenir — kullanıcı aynı adı iki kez
     * yazdığında künyede iki kez görünmesin.
     *
     * @return list<string>|null
     */
    private function technologies(array $data): ?array
    {
        $items = collect($data['technologies'] ?? [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $items ?: null;
    }

    /**
     * Sonuç blokları. Etiketi olmayan satır yok sayılır — repeater boş bir
     * satırla açıldığı için kullanıcı hiç doldurmadan kaydedebilir.
     *
     * @return list<array{label: string, value: string, direction: string}>|null
     */
    private function results(array $data): ?array
    {
        $rows = collect($data['results'] ?? [])
            ->filter(fn ($row) => is_array($row) && filled($row['label'] ?? null))
            ->map(fn ($row) => [
                'label' => trim((string) $row['label']),
                'value' => trim((string) ($row['value'] ?? '')),
                'direction' => array_key_exists($row['direction'] ?? '', Project::DIRECTIONS)
                    ? $row['direction']
                    : 'neutral',
            ])
            ->values()
            ->all();

        return $rows ?: null;
    }

    /** Paylaşılan bileşenlerin kaydı: kapak, galeri, video, etiket, SEO, SSS ve hizmetler. */
    private function syncRelations(Project $project, array $data): void
    {
        $project->syncMedia($data['cover_media_id'] ?? null, 'cover');
        $project->syncMedia(
            $data['gallery_media_ids'] ?? [],
            'gallery',
            $data['gallery_media_ids_cover'] ?? null,
        );
        $project->syncMedia($data['video_media_id'] ?? null, 'video');
        $project->syncTags($data['tags'] ?? []);
        $project->syncSeo($data['seo'] ?? []);
        $project->syncFaqs($data['faqs'] ?? []);
        $project->services()->sync($data['services'] ?? []);
    }
}
