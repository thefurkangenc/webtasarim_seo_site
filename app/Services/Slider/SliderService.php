<?php

namespace App\Services\Slider;

use App\Models\Slider\Slider;
use App\Services\Concerns\ReordersRecords;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SliderService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return Slider::query()
            ->with('media')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($query) => $query->where('title', 'like', "%{$term}%")
                    ->orWhere('slogan', 'like', "%{$term}%")
            ))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Slider $slider) => $slider->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?Slider $slider): array
    {
        return ['slider' => $slider];
    }

    public function create(array $data): Slider
    {
        return DB::transaction(function () use ($data) {
            $slider = Slider::create($this->attributes($data));
            $slider->syncMedia($data['desktop_media_id'] ?? null, 'desktop');
            $slider->syncMedia($data['mobile_media_id'] ?? null, 'mobile');

            return $slider->load('media');
        });
    }

    public function update(Slider $slider, array $data): Slider
    {
        return DB::transaction(function () use ($slider, $data) {
            $slider->update($this->attributes($data));
            $slider->syncMedia($data['desktop_media_id'] ?? null, 'desktop');
            $slider->syncMedia($data['mobile_media_id'] ?? null, 'mobile');

            return $slider->load('media');
        });
    }

    public function delete(Slider $slider): void
    {
        DB::transaction(function () use ($slider) {
            $slider->syncMedia(null, 'desktop');
            $slider->syncMedia(null, 'mobile');
            $slider->delete();
        });
    }

    /** Ön yüz için: yayındaki slaytları sırasıyla döndürür. */
    public function active(): Collection
    {
        return Slider::query()
            ->where('is_active', true)
            ->with('media')
            ->orderBy('sort_order')
            ->get();
    }

    /** @return array<string, mixed> */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'slogan' => $data['slogan'] ?? null,
            'description' => $data['description'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'button_url' => $data['button_url'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    protected function reorderModel(): string
    {
        return Slider::class;
    }
}
