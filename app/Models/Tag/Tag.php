<?php

namespace App\Models\Tag;

use App\Models\Blog\Blog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[Fillable(['name', 'slug', 'is_active'])]
class Tag extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Etikete bağlı blog yazıları. Etiket sözlüğü modüller arası paylaşılır
     * (aynı etiket hizmete de bağlanabilir); bu ilişki yalnızca blog tarafını
     * verir — etiket sayfası ve kenar çubuğu sayıları bunu kullanır.
     */
    public function blogs(): MorphToMany
    {
        return $this->morphedByMany(Blog::class, 'taggable');
    }
}
