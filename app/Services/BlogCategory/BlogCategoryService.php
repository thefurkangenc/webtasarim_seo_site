<?php

namespace App\Services\BlogCategory;

use App\Models\BlogCategory\BlogCategory;
use App\Services\Concerns\ReordersRecords;
use App\Support\Slug;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BlogCategoryService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return BlogCategory::query()
            ->withCount('blogs')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '',
                fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (BlogCategory $category) => $category->toPayload());
    }

    public function create(array $data): BlogCategory
    {
        return DB::transaction(function () use ($data) {
            $category = BlogCategory::create([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['name'], 'blog_categories'),
            ]);

            $category->syncSeo($data['seo'] ?? []);

            return $category;
        });
    }

    public function update(BlogCategory $category, array $data): BlogCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $category->update([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['name'], 'blog_categories', $category->id),
            ]);

            $category->syncSeo($data['seo'] ?? []);

            return $category;
        });
    }

    /** Yazısı olan kategori silinmez; önce yazılar taşınmalı. */
    public function delete(BlogCategory $category): void
    {
        if ($category->blogs()->exists()) {
            throw new DomainException('Bu kategoride yazılar var. Önce yazıları başka bir kategoriye taşıyın.');
        }

        DB::transaction(function () use ($category) {
            $category->seo()->delete();
            $category->delete();
        });
    }

    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    protected function reorderModel(): string
    {
        return BlogCategory::class;
    }
}
