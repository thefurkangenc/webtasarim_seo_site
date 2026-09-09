<?php

namespace App\Models\Concerns;

use App\Models\Faq\Faq;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Modele mevcut FAQ havuzundan çoklu soru bağlama yeteneği verir. HasTags ile
 * birebir aynı kalıp — tek fark etiketler serbest metinken FAQ'lar önceden
 * var olan kayıtlardan seçilir (bkz. <x-admin::form.faqs>).
 *
 *   class Blog extends Model { use HasFaqs; }
 *
 *   $blog->syncFaqs([3, 7, 12]);   // sırayla bağlanır, listede olmayan kopar
 *   $blog->faqs;                   // soru sırasına göre (pivot sort_order)
 */
trait HasFaqs
{
    public function faqs(): MorphToMany
    {
        return $this->morphToMany(Faq::class, 'faqable')
            ->withPivot('sort_order')
            ->orderBy('faqables.sort_order');
    }

    /** @param  array<int, int>|null  $ids  Seçilme sırasıyla FAQ kimlikleri */
    public function syncFaqs(?array $ids): void
    {
        $ids = array_values(array_unique(array_filter((array) $ids)));

        $this->faqs()->sync(
            collect($ids)->mapWithKeys(fn (int $id, int $index) => [$id => ['sort_order' => $index]])->all(),
        );

        $this->unsetRelation('faqs');
    }
}
