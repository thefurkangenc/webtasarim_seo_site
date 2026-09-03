<?php

namespace App\Models\Concerns;

use App\Models\Seo\Seo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Modele SEO alanları ekler. Modül tablosuna kolon açılmaz —
 * her şey tek bir `seo` tablosunda durur.
 *
 *   class Blog extends Model { use HasSeo; }
 *
 *   $blog->syncSeo($data['seo'] ?? []);
 *   $blog->seoMeta();   // ön yüzde <meta> etiketleri için
 *
 * Boş bırakılan meta başlık/açıklama, okurken modelin kendi alanına düşer.
 * Kaynak alanlar $seoFallbacks ile modelde değiştirilebilir.
 */
trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function syncSeo(array $data): void
    {
        if ($data === []) {
            return;
        }

        $this->seo()->updateOrCreate([], [
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'robots_index' => (bool) ($data['robots_index'] ?? true),
            'robots_follow' => (bool) ($data['robots_follow'] ?? true),
            // Boş string de null olmalı; form seçim yapılmadığında '' gönderir.
            'og_media_id' => ($data['og_media_id'] ?? null) ?: null,
        ]);

        $this->unsetRelation('seo');
    }

    /**
     * Ön yüzde doğrudan basılabilecek çözümlenmiş değerler.
     *
     * @return array<string, string|null>
     */
    public function seoMeta(): array
    {
        $seo = $this->seo;
        $fallbacks = $this->seoFallbacks();

        return [
            'title' => $seo?->meta_title ?: $fallbacks['title'],
            'description' => $seo?->meta_description ?: $fallbacks['description'],
            'keywords' => $seo?->meta_keywords,
            'canonical' => $seo?->canonical_url,
            'robots' => $seo?->robots() ?? 'index,follow',
            'image' => $seo?->ogMedia?->url() ?? $fallbacks['image'],
        ];
    }

    /**
     * Meta alanları boşken kullanılacak kaynaklar. Alan adları farklı olan
     * modeller bu metodu ezer.
     *
     * @return array<string, string|null>
     */
    protected function seoFallbacks(): array
    {
        return [
            'title' => $this->title ?? $this->name ?? null,
            'description' => $this->excerpt ?? $this->description ?? null,
            'image' => method_exists($this, 'mediaUrl') ? $this->mediaUrl('cover') : null,
        ];
    }
}
