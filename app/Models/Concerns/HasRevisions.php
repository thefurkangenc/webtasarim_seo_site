<?php

namespace App\Models\Concerns;

use App\Models\Revision\Revision;
use App\Services\Revision\RevisionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Kayıt geçmişi ve geri alma.
 *
 * Modele tek satır eklenir:
 *
 *     class Blog extends Model { use HasRevisions; }
 *
 * Anlık görüntü kayıt DEĞİŞMEDEN ÖNCE, `saving` olayında alınır. Bunun iki
 * sebebi var: (1) o anda veritabanındaki hal hâlâ eski sürümdür, (2) SEO /
 * etiket / medya bağları servis tarafından kaydetmeden SONRA güncellendiği
 * için "kaydetme sonrası" bir görüntü ilişkileri karışık yakalardı.
 *
 * `saveQuietly` ile yazan toplu işler (tarama, sayaç güncellemesi) olay
 * fırlatmadığı için geçmişi şişirmez.
 */
trait HasRevisions
{
    public static function bootHasRevisions(): void
    {
        static::saving(function (Model $model) {
            if ($model->exists) {
                app(RevisionService::class)->capture($model);
            }
        });
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable')->latest('id');
    }

    /**
     * Kaydın veritabanındaki hali: kolonlar + paylaşılan bileşenler.
     * `saving` içinde çağrıldığında `getOriginal()` henüz eski değerleri
     * verir — geri yüklenecek olan da budur.
     *
     * @return array<string, mixed>
     */
    public function revisionSnapshot(): array
    {
        return [
            'attributes' => collect($this->getOriginal())
                ->except(config('revisions.ignore', []))
                ->map(fn (mixed $value) => $value instanceof \DateTimeInterface ? (string) $value : $value)
                ->all(),
            'seo' => $this->revisionSeo(),
            'tags' => method_exists($this, 'tagNames') ? $this->tags()->pluck('name')->all() : [],
            'faqs' => method_exists($this, 'syncFaqs') ? $this->faqs()->pluck('faqs.id')->all() : [],
            'media' => $this->revisionMedia(),
            'extra' => $this->revisionExtra(),
        ];
    }

    /**
     * Anlık görüntüyü modülün kendi servisinin `update()` metodunun
     * beklediği forma çevirir — slug üretimi, otomatik 301 ve alt ağaç
     * yeniden yazımı gibi kurallar orada yaşıyor, burada kopyalanmaz.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public function revisionRestorePayload(array $snapshot): array
    {
        $media = [];

        foreach ($snapshot['media'] ?? [] as $collection => $entry) {
            // Tek görselli koleksiyonlar (cover, logo, photo) tek id bekler.
            $media[$collection.'_media_id'] = count($entry['ids']) === 1 ? $entry['ids'][0] : $entry['ids'];
        }

        return [
            ...$snapshot['attributes'] ?? [],
            'seo' => $snapshot['seo'] ?? [],
            'tags' => $snapshot['tags'] ?? [],
            'faqs' => $snapshot['faqs'] ?? [],
            ...$media,
            ...$this->revisionExtraPayload($snapshot['extra'] ?? []),
        ];
    }

    /** Listede ve karşılaştırmada görünen ad. */
    public function revisionLabel(): string
    {
        return (string) ($this->title ?? $this->name ?? $this->question ?? 'Kayıt #'.$this->getKey());
    }

    /**
     * Modele özel ek ilişkiler (Hizmet bölgeleri gibi). Varsayılan: yok.
     *
     * @return array<string, mixed>
     */
    protected function revisionExtra(): array
    {
        return [];
    }

    /**
     * Ek ilişkilerin servis payload'ındaki karşılığı.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function revisionExtraPayload(array $extra): array
    {
        return [];
    }

    /** @return array<string, mixed>|null */
    private function revisionSeo(): ?array
    {
        if (! method_exists($this, 'syncSeo')) {
            return null;
        }

        $seo = $this->seo()->first();

        return $seo?->only([
            'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
            'robots_index', 'robots_follow', 'og_media_id',
            'schema_type', 'schema_json', 'schema_override', 'focus_keyword',
        ]);
    }

    /**
     * Koleksiyon => bağlı medya kimlikleri. Dosyanın kendisi medya
     * kütüphanesinde yaşar; revizyon yalnızca bağı saklar.
     *
     * @return array<string, array{ids: array<int, int>, cover_id: int|null}>
     */
    private function revisionMedia(): array
    {
        if (! method_exists($this, 'syncMedia')) {
            return [];
        }

        return $this->media()
            ->get()
            ->groupBy('pivot.collection')
            ->map(fn ($items) => [
                'ids' => $items->sortBy('pivot.sort_order')->pluck('id')->values()->all(),
                'cover_id' => $items->firstWhere('pivot.is_cover', true)?->id,
            ])
            ->all();
    }
}
