<?php

namespace App\Services\Testimonial;

use App\Models\Testimonial\Testimonial;
use App\Services\Concerns\ReordersRecords;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TestimonialService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return Testimonial::query()
            ->with('media')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%")
                    ->orWhere('content', 'like', "%{$term}%")
            ))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Testimonial $testimonial) => $testimonial->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?Testimonial $testimonial): array
    {
        return ['testimonial' => $testimonial];
    }

    public function create(array $data): Testimonial
    {
        return DB::transaction(function () use ($data) {
            $testimonial = Testimonial::create($this->attributes($data));
            $testimonial->syncMedia($data['photo_media_id'] ?? null, 'photo');

            return $testimonial->load('media');
        });
    }

    public function update(Testimonial $testimonial, array $data): Testimonial
    {
        return DB::transaction(function () use ($testimonial, $data) {
            $testimonial->update($this->attributes($data));
            $testimonial->syncMedia($data['photo_media_id'] ?? null, 'photo');

            return $testimonial->load('media');
        });
    }

    public function delete(Testimonial $testimonial): void
    {
        DB::transaction(function () use ($testimonial) {
            $testimonial->syncMedia(null, 'photo');
            $testimonial->delete();
        });
    }

    /** Ön yüz için: tüm yorumları sıralarıyla döndürür. */
    public function active(): Collection
    {
        return Testimonial::query()
            ->with('media')
            ->orderBy('sort_order')
            ->get();
    }

    /** @return array<string, mixed> */
    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'title' => $data['title'] ?? null,
            'content' => $data['content'],
            'rating' => $data['rating'] ?? 5,
        ];
    }

    protected function reorderModel(): string
    {
        return Testimonial::class;
    }
}
