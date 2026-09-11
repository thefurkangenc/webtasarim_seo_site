<?php

namespace App\Services\WhyChooseUs;

use App\Models\WhyChooseUs\WhyChooseUs;
use App\Services\Concerns\ReordersRecords;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WhyChooseUsService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return WhyChooseUs::query()
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($query) => $query->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
            ))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (WhyChooseUs $item) => $item->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?WhyChooseUs $whyChooseUs): array
    {
        return ['whyChooseUs' => $whyChooseUs];
    }

    public function create(array $data): WhyChooseUs
    {
        return WhyChooseUs::create($this->attributes($data));
    }

    public function update(WhyChooseUs $whyChooseUs, array $data): WhyChooseUs
    {
        $whyChooseUs->update($this->attributes($data));

        return $whyChooseUs;
    }

    public function delete(WhyChooseUs $whyChooseUs): void
    {
        $whyChooseUs->delete();
    }

    /** Ön yüz için: tüm kayıtları sıralarıyla döndürür. */
    public function active(): Collection
    {
        return WhyChooseUs::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /** @return array<string, mixed> */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    protected function reorderModel(): string
    {
        return WhyChooseUs::class;
    }
}
