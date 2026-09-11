<?php

namespace App\Services\Revision;

use App\Models\Revision\Revision;
use App\Support\Activity;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Revizyonların yazılması, karşılaştırılması ve geri yüklenmesi.
 *
 * Yazma tek kapıdan geçer: modeller HasRevisions trait'i üzerinden burayı
 * çağırır. Geri yükleme ise kaydı doğrudan güncellemez — modülün kendi
 * servisine devreder, böylece slug üretimi, otomatik 301 ve alt sayfa
 * yeniden yazımı gibi kurallar tek yerde kalır.
 */
class RevisionService
{
    /**
     * Aynı istekte aynı kaydın ikinci kez görüntüsü alınmaz: bir düzenleme
     * modeli birkaç kez kaydedebilir (ör. sayfa ağacı yeniden yazılırken),
     * geçmişte bunun tek bir satır olması gerekir.
     *
     * @var array<string, true>
     */
    private static array $captured = [];

    /**
     * Koruma isteğin ömrüyle sınırlıdır. Web isteğinde süreç zaten biter, ama
     * `queue:work` gibi UZUN YAŞAYAN süreçlerde temizlenmezse ilk kaydetmeden
     * sonra o kaydın hiçbir değişikliği geçmişe yazılmaz. Bu yüzden her
     * kuyruk işinden önce sıfırlanır (AppServiceProvider'da bağlı).
     */
    public static function flushCaptured(): void
    {
        self::$captured = [];
    }

    /** Kaydın değişmeden önceki halini saklar. */
    public function capture(Model $model): void
    {
        $key = $model::class.':'.$model->getKey();

        if (isset(self::$captured[$key])) {
            return;
        }

        self::$captured[$key] = true;

        $snapshot = $model->revisionSnapshot();
        $hash = sha1((string) json_encode($snapshot));
        $previous = $model->revisions()->first();

        // İçerik gerçekten değişmediyse (ilişkisiz bir kaydetme, sıralama,
        // sayaç güncellemesi) yeni sürüm açmaya gerek yok.
        if ($previous?->hash === $hash) {
            return;
        }

        Revision::create([
            'revisionable_type' => $model::class,
            'revisionable_id' => $model->getKey(),
            'user_id' => auth()->id(),
            'snapshot' => $snapshot,
            'changed_keys' => $previous ? $this->changedKeys($previous->snapshot, $snapshot) : null,
            'hash' => $hash,
            // Etiket de o sürüme ait olmalı: modelin üzerindeki başlık artık
            // yeni değeri taşıyor, geçmişte eskisi görünmeli.
            'label' => $this->label($snapshot, $model),
        ]);

        $this->prune($model);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $module = $filters['module'] ?? null;

        return Revision::query()
            ->with(['user:id,name', 'revisionable'])
            ->when($module, fn ($query) => $query->where('revisionable_type', config("revisions.models.{$module}.class")))
            ->when($filters['subject_type'] ?? null, fn ($query, $type) => $query->where('revisionable_type', $type))
            ->when($filters['subject_id'] ?? null, fn ($query, $id) => $query->where('revisionable_id', $id))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('label', 'like', '%'.$search.'%'))
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20)
            ->through(fn (Revision $revision) => $revision->toPayload());
    }

    /** İndex ekranının açılır listeleri ve özeti. */
    public function indexData(): array
    {
        return [
            'modules' => collect(config('revisions.models'))->map(fn (array $meta) => $meta['label'])->all(),
            'total' => Revision::count(),
            'keep' => (int) config('revisions.keep', 25),
        ];
    }

    /**
     * Karşılaştırma: seçilen sürüm ile kaydın ŞU ANKİ hali. Kullanıcının
     * geri yüklemeden önce görmek istediği tam olarak budur.
     *
     * @return array<string, mixed>
     */
    public function detail(Revision $revision): array
    {
        $model = $revision->revisionable;
        $current = $model?->revisionSnapshot();
        $old = $this->flatten($revision->snapshot);
        $new = $current ? $this->flatten($current) : [];
        $fields = [];

        foreach (array_keys($old + $new) as $key) {
            $before = $old[$key] ?? null;
            $after = $new[$key] ?? null;

            if ($this->same($before, $after)) {
                continue;
            }

            $fields[] = [
                'key' => $key,
                // Nokta içeren anahtarlar (seo.meta_title) config()'in yol
                // çözümlemesine takılır; dizi doğrudan okunur.
                'label' => config('revisions.labels')[$key] ?? $key,
                'old' => $this->preview($before),
                'new' => $this->preview($after),
            ];
        }

        return [
            ...$revision->toPayload(),
            'fields' => $fields,
            'restorable' => $model !== null,
            'identical' => $model !== null && $fields === [],
        ];
    }

    /**
     * Seçilen sürümü geri yükler. Geri yükleme de bir değişikliktir:
     * mevcut hal, geri alınabilsin diye kendiliğinden yeni bir revizyon
     * olarak saklanır (model olayı zinciri üzerinden).
     */
    public function restore(Revision $revision): Model
    {
        $model = $revision->revisionable;

        if ($model === null) {
            throw new DomainException('Kayıt silinmiş; bu sürüm geri yüklenemez.');
        }

        $payload = $model->revisionRestorePayload($revision->snapshot);
        $service = config("revisions.models.{$revision->moduleKey()}.service");

        if ($service) {
            app($service)->update($model, $payload);
        } else {
            $this->restoreDirectly($model, $revision->snapshot);
        }

        Activity::record(
            logName: method_exists($model, 'activityLogName') ? $model->activityLogName() : 'revision',
            event: 'restored',
            description: $revision->created_at->format('d.m.Y H:i').' tarihli sürüm geri yüklendi',
            subject: $model,
            subjectLabel: $revision->label,
            properties: ['new' => ['revision_id' => $revision->id]],
            severity: 'notice',
        );

        return $model->refresh();
    }

    /**
     * Servisi olmayan modeller için: kolonları yazar, paylaşılan
     * bileşenleri elle senkronlar.
     *
     * @param  array<string, mixed>  $snapshot
     */
    private function restoreDirectly(Model $model, array $snapshot): void
    {
        $model->forceFill($snapshot['attributes'] ?? [])->save();

        if (method_exists($model, 'syncSeo')) {
            $model->syncSeo($snapshot['seo'] ?? []);
        }

        if (method_exists($model, 'syncTags')) {
            $model->syncTags($snapshot['tags'] ?? []);
        }

        if (method_exists($model, 'syncFaqs')) {
            $model->syncFaqs($snapshot['faqs'] ?? []);
        }

        foreach ($snapshot['media'] ?? [] as $collection => $entry) {
            $model->syncMedia($entry['ids'], $collection, $entry['cover_id'] ?? null);
        }
    }

    /**
     * Sürümün listede görünen adı — anlık görüntüdeki başlıktan okunur.
     *
     * @param  array<string, mixed>  $snapshot
     */
    private function label(array $snapshot, Model $model): string
    {
        $attributes = $snapshot['attributes'] ?? [];

        foreach (['title', 'name', 'question'] as $key) {
            if (filled($attributes[$key] ?? null)) {
                return (string) $attributes[$key];
            }
        }

        return 'Kayıt #'.$model->getKey();
    }

    /** Kayıt başına son N sürüm tutulur; fazlası silinir. */
    private function prune(Model $model): void
    {
        $keep = (int) config('revisions.keep', 25);

        $stale = $model->revisions()
            ->skip($keep)
            ->take(100)
            ->pluck('id');

        if ($stale->isNotEmpty()) {
            Revision::whereKey($stale)->delete();
        }
    }

    /**
     * İki sürüm arasında değişen alan anahtarları.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<int, string>
     */
    private function changedKeys(array $before, array $after): array
    {
        $old = $this->flatten($before);
        $new = $this->flatten($after);

        return array_values(array_filter(
            array_keys($old + $new),
            fn (string $key) => ! $this->same($old[$key] ?? null, $new[$key] ?? null),
        ));
    }

    /**
     * Anlık görüntüyü karşılaştırılabilir tek düzey diziye indirger:
     * `content`, `seo.meta_title`, `tags`, `media.cover` gibi.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    private function flatten(array $snapshot): array
    {
        $flat = $snapshot['attributes'] ?? [];

        foreach ($snapshot['seo'] ?? [] as $key => $value) {
            $flat['seo.'.$key] = $value;
        }

        $flat['tags'] = implode(', ', $snapshot['tags'] ?? []);
        $flat['faqs'] = implode(', ', $snapshot['faqs'] ?? []);

        foreach ($snapshot['media'] ?? [] as $collection => $entry) {
            $flat['media.'.$collection] = implode(', ', $entry['ids'] ?? []);
        }

        foreach ($snapshot['extra'] ?? [] as $key => $value) {
            $flat[$key] = is_array($value) ? implode(', ', $value) : $value;
        }

        return $flat;
    }

    /** "boş" değerlerin hepsi (null, '', '0000-00-00') aynı sayılır. */
    private function same(mixed $a, mixed $b): bool
    {
        if (is_array($a) || is_array($b)) {
            return json_encode($a) === json_encode($b);
        }

        return (string) ($a ?? '') === (string) ($b ?? '');
    }

    private function preview(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = is_array($value) ? (string) json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');

        return mb_strimwidth($text, 0, (int) config('revisions.preview_length', 600), '…');
    }
}
