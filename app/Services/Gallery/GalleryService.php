<?php

namespace App\Services\Gallery;

use App\Models\Gallery\Gallery;
use App\Services\Concerns\ReordersRecords;
use App\Support\Slug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GalleryService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return Gallery::query()
            ->with(['media', 'seo'])
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%"),
            ))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Gallery $gallery) => $gallery->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?Gallery $gallery): array
    {
        $gallery?->load(['media', 'seo']);

        return ['gallery' => $gallery];
    }

    public function create(array $data): Gallery
    {
        return DB::transaction(function () use ($data) {
            $gallery = Gallery::create([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['title'], 'galleries'),
            ]);

            $this->syncRelations($gallery, $data);

            return $gallery;
        });
    }

    public function update(Gallery $gallery, array $data): Gallery
    {
        return DB::transaction(function () use ($gallery, $data) {
            $gallery->update([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['title'], 'galleries', $gallery->id),
            ]);

            $this->syncRelations($gallery, $data);

            return $gallery;
        });
    }

    public function delete(Gallery $gallery): void
    {
        DB::transaction(function () use ($gallery) {
            $gallery->syncMedia([], 'gallery');
            $gallery->seo()->delete();
            $gallery->delete();
        });
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        return [
            'total' => Gallery::count(),
            'published' => Gallery::where('status', Gallery::STATUS_PUBLISHED)->count(),
            'draft' => Gallery::where('status', Gallery::STATUS_DRAFT)->count(),
        ];
    }

    protected function reorderModel(): string
    {
        return Gallery::class;
    }

    /** `sort_order` burada yok: formda girilmez, HasSortOrder verir. */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? Gallery::STATUS_DRAFT,
        ];
    }

    private function syncRelations(Gallery $gallery, array $data): void
    {
        $gallery->syncMedia(
            $data['gallery_media_ids'] ?? [],
            'gallery',
            $data['gallery_media_ids_cover'] ?? null,
        );
        $gallery->syncSeo($data['seo'] ?? []);
    }
}
