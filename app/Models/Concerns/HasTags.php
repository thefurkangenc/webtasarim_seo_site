<?php

namespace App\Models\Concerns;

use App\Models\Tag\Tag;
use App\Services\Tag\TagService;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Modele etiket bağlama yeteneği verir. Etiketler tüm modüller arasında
 * paylaşılır; aynı etiket hem bloga hem hizmete bağlanabilir.
 *
 *   class Blog extends Model { use HasTags; }
 *
 *   $blog->syncTags(['laravel', 'seo']);   // olmayan etiket oluşturulur
 *   $blog->tagNames();                     // ['laravel', 'seo']
 */
trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->withPivot('sort_order')
            ->orderBy('taggables.sort_order');
    }

    /**
     * Etiket adlarını modele bağlar. Adlar TagService'te normalize edilir;
     * mevcut olmayanlar oluşturulur, listede olmayanların bağı kopar.
     *
     * @param  array<int, string>|string|null  $names
     */
    public function syncTags(array|string|null $names): void
    {
        $ids = app(TagService::class)->resolve($names);

        $this->tags()->sync(
            collect($ids)->mapWithKeys(fn (int $id, int $index) => [$id => ['sort_order' => $index]])->all(),
        );

        $this->unsetRelation('tags');
    }

    /** @return array<int, string> */
    public function tagNames(): array
    {
        return $this->tags->pluck('name')->all();
    }
}
