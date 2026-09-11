<?php

namespace App\Services\Reference;

use App\Models\Reference\Reference;
use App\Services\Concerns\ReordersRecords;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReferenceService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return Reference::query()
            ->with('media')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Reference $reference) => $reference->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?Reference $reference): array
    {
        return ['reference' => $reference];
    }

    public function create(array $data): Reference
    {
        return DB::transaction(function () use ($data) {
            $reference = Reference::create($this->attributes($data));
            $reference->syncMedia($data['logo_media_id'] ?? null, 'logo');

            return $reference->load('media');
        });
    }

    public function update(Reference $reference, array $data): Reference
    {
        return DB::transaction(function () use ($reference, $data) {
            $reference->update($this->attributes($data));
            $reference->syncMedia($data['logo_media_id'] ?? null, 'logo');

            return $reference->load('media');
        });
    }

    public function delete(Reference $reference): void
    {
        DB::transaction(function () use ($reference) {
            $reference->syncMedia(null, 'logo');
            $reference->delete();
        });
    }

    /** Ön yüz için: tüm referansları sıralarıyla döndürür. */
    public function active(): Collection
    {
        return Reference::query()
            ->where('is_active', true)
            ->with('media')
            ->orderBy('sort_order')
            ->get();
    }

    /** @return array<string, mixed> */
    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'url' => $data['url'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    protected function reorderModel(): string
    {
        return Reference::class;
    }
}
