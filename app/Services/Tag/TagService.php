<?php

namespace App\Services\Tag;

use App\Models\Tag\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Etiket çözümleme. Etiket alanı kullanıcıdan serbest metin aldığı için
 * eşleştirme slug üzerinden yapılır — "Web Tasarım", "web tasarım" ve
 * "WEB TASARIM" aynı etikete düşer.
 */
class TagService
{
    /**
     * Verilen adları etiket kimliklerine çevirir, olmayanları oluşturur.
     *
     * @param  array<int, string>|string|null  $names
     * @return array<int, int>
     */
    public function resolve(array|string|null $names): array
    {
        return $this->normalize($names)
            ->map(fn (array $tag) => Tag::firstOrCreate(
                ['slug' => $tag['slug']],
                ['name' => $tag['name'], 'is_active' => true],
            )->id)
            ->all();
    }

    /** Etiket alanının öneri listesi. */
    public function search(?string $term, int $limit = 10): Collection
    {
        return Tag::query()
            ->where('is_active', true)
            ->when($term, fn ($query) => $query->where('name', 'like', '%'.$term.'%'))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'slug']);
    }

    /**
     * Virgülle ayrılmış metni ya da diziyi tekilleştirilmiş ad/slug çiftlerine
     * çevirir. Boşlar ve 50 karakteri aşanlar elenir.
     *
     * @param  array<int, string>|string|null  $names
     * @return Collection<int, array{name: string, slug: string}>
     */
    private function normalize(array|string|null $names): Collection
    {
        return collect(is_string($names) ? explode(',', $names) : ($names ?? []))
            ->map(fn ($name) => trim(preg_replace('/\s+/u', ' ', (string) $name)))
            ->filter(fn (string $name) => $name !== '' && mb_strlen($name) <= 50)
            ->map(fn (string $name) => ['name' => $name, 'slug' => Str::slug($name, '-', 'tr')])
            ->filter(fn (array $tag) => $tag['slug'] !== '')
            ->unique('slug')
            ->values();
    }
}
