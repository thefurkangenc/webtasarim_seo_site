<?php

namespace App\Services\ProjectCategory;

use App\Models\ProjectCategory\ProjectCategory;
use App\Services\Concerns\ReordersRecords;
use App\Support\Slug;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProjectCategoryService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return ProjectCategory::query()
            ->withCount('projects')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '',
                fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (ProjectCategory $category) => $category->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?ProjectCategory $category): array
    {
        return ['category' => $category];
    }

    public function create(array $data): ProjectCategory
    {
        return DB::transaction(function () use ($data) {
            $category = ProjectCategory::create([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['name'], 'project_categories'),
            ]);

            $category->syncSeo($data['seo'] ?? []);

            return $category;
        });
    }

    public function update(ProjectCategory $category, array $data): ProjectCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $category->update([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['name'], 'project_categories', $category->id),
            ]);

            $category->syncSeo($data['seo'] ?? []);

            return $category;
        });
    }

    /** Projesi olan kategori silinmez; önce projeler taşınmalı. */
    public function delete(ProjectCategory $category): void
    {
        if ($category->projects()->exists()) {
            throw new DomainException('Bu kategoride projeler var. Önce projeleri başka bir kategoriye taşıyın.');
        }

        DB::transaction(function () use ($category) {
            $category->seo()->delete();
            $category->delete();
        });
    }

    /** Form select'leri için: kimlik => ad. */
    public function options(): array
    {
        return ProjectCategory::where('is_active', true)->orderBy('sort_order')->pluck('name', 'id')->all();
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
        return ProjectCategory::class;
    }
}
