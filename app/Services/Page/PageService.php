<?php

namespace App\Services\Page;

use App\Models\Page\Page;
use App\Services\Concerns\ReordersRecords;
use App\Support\Tree;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sayfa yöneticisi.
 *
 * `path` kolonunun tek yazarı burasıdır: slug ya da üst sayfa değişince yol
 * yeniden türetilir ve alt ağacın tamamı onunla birlikte güncellenir. Ön yüz
 * hiçbir zaman ağacı gezmez, yalnızca `path`'e bakar.
 */
class PageService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return Page::query()
            ->with(['author:id,name', 'seo'])
            ->withCount('children')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$term}%")
                    ->orWhere('path', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%"),
            ))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['template'] ?? null, fn ($query, $template) => $query->where('template', $template))
            // "Yalnızca kök sayfalar" filtresi: 0 gelirse parent_id boş olanlar.
            ->when(array_key_exists('parent_id', $filters) && $filters['parent_id'] !== null,
                fn ($query) => (int) $filters['parent_id'] === 0
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', (int) $filters['parent_id']))
            // Varsayılan sıra `path`: alfabetik yol sıralaması aynı zamanda
            // ağaç sırasıdır ("a", "a/b", "a/c", "b"), listede girintili
            // görünüm için ikinci bir sorgu gerekmez.
            ->orderBy($filters['sort'] ?? 'path', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 20)
            ->through(fn (Page $page) => $page->toPayload());
    }

    public function create(array $data): Page
    {
        return DB::transaction(function () use ($data) {
            $parent = $this->parent($data);
            $slug = $this->uniqueSlug($this->slugSource($data), $parent?->id);

            $page = Page::create([
                ...$this->attributes($data),
                'parent_id' => $parent?->id,
                'slug' => $slug,
                'path' => $this->resolvePath($parent, $slug),
                'user_id' => auth()->id(),
            ]);

            $this->syncRelations($page, $data);

            return $page;
        });
    }

    public function update(Page $page, array $data): Page
    {
        return DB::transaction(function () use ($page, $data) {
            $parent = $this->parent($data);
            $slug = $this->uniqueSlug($this->slugSource($data), $parent?->id, $page->id);

            $page->update([
                ...$this->attributes($data),
                'parent_id' => $parent?->id,
                'slug' => $slug,
                'path' => $this->resolvePath($parent, $slug),
            ]);

            // Yol değiştiyse altındaki her şeyin yolu da değişti.
            $this->rewriteSubtree($page);

            $this->syncRelations($page, $data);

            return $page;
        });
    }

    /**
     * Sayfayı siler; altındaki sayfaları silmez, bir üst seviyeye çıkarır.
     * Bir bölümün üst sayfasını silmek alt sayfaların tamamını uçurmasın —
     * geri alınamaz bir veri kaybı olur.
     */
    public function delete(Page $page): void
    {
        DB::transaction(function () use ($page) {
            $this->promoteChildren($page);

            $page->syncMedia(null, 'cover');
            $page->tags()->detach();
            $page->faqs()->detach();
            $page->seo()->delete();
            $page->delete();
        });
    }

    /**
     * Üst sayfa select'inin seçenekleri: ağaç sırasında, girintili.
     *
     * Düzenlenen sayfanın kendisi ve altındaki her şey listeden düşer
     * (Tree::options o dala hiç inmez) — bir sayfa kendi torununun çocuğu
     * olamaz, olursa yol çözümlemesi sonsuz döngüye girer. Ayrıca derinlik
     * sınırına dayanmış sayfalar da düşer: altına eklenen çocuk sınırı aşardı.
     *
     * @return array<int, array{label: string, depth: int}>
     */
    public function parentOptions(?Page $page = null): array
    {
        $maxParentDepth = max(0, (int) config('pages.max_depth', 3) - 2);

        return array_filter(
            Tree::options(
                Page::query()->orderBy('sort_order')->orderBy('title')->get(['id', 'parent_id', 'title']),
                'title',
                $page?->id,
            ),
            fn (array $option) => $option['depth'] <= $maxParentDepth,
        );
    }

    /**
     * Üst sayfa kimliği -> yolu. Form, üst sayfa seçimi değiştikçe adres
     * satırını sunucuya gitmeden güncellemek için bunu kullanır.
     *
     * @return array<int, string>
     */
    public function parentPaths(): array
    {
        return Page::query()->pluck('path', 'id')->all();
    }

    /**
     * Bu sayfanın altındaki en derin torunun kaç kat aşağıda olduğu —
     * 0 demek çocuğu yok. Üst sayfa değiştirilirken taşınan dalın sınırı
     * aşıp aşmadığını anlamak için kullanılır (bkz. PageCreateRequest).
     */
    public function subtreeHeight(Page $page): int
    {
        // Derinlik yoldaki '/' sayısı; SQL'de saymak sürücüye bağımlı bir
        // ifade gerektirdiği için yollar çekilip PHP'de sayılıyor. Bir dalın
        // altındaki kayıt sayısı her zaman küçük, maliyeti yok.
        $depths = Page::where('path', 'like', $page->path.'/%')
            ->pluck('path')
            ->map(fn (string $path) => substr_count($path, '/'));

        return $depths->isEmpty() ? 0 : $depths->max() - $page->depth();
    }

    /* --------------------------------------------------------------------
     | Ön yüz
     * -------------------------------------------------------------------- */

    /**
     * URL'den sayfa. Taslak ya da yayın tarihi gelmemiş sayfa için null
     * döner — ön yüz bunu 404'e çevirir.
     */
    public function findByPath(string $path): ?Page
    {
        return Page::visible()
            ->where('path', trim($path, '/'))
            ->with(['media', 'seo.ogMedia', 'faqs', 'tags'])
            ->first();
    }

    /**
     * Breadcrumb için üst sayfalar, kökten aşağıya. Ağaç gezilmez: yolun
     * kümülatif önekleri tek `whereIn` ile çekilir.
     *
     * @return Collection<int, Page>
     */
    public function ancestors(Page $page): Collection
    {
        $paths = $page->ancestorPaths();

        if ($paths === []) {
            return new Collection;
        }

        return Page::visible()
            ->whereIn('path', $paths)
            ->get(['id', 'title', 'path'])
            // whereIn sıra garantisi vermez; kökten aşağıya diziliş yol
            // uzunluğuna göre yeniden kurulur.
            ->sortBy(fn (Page $ancestor) => mb_strlen($ancestor->path))
            ->values();
    }

    /**
     * Ön yüz şablonunun ihtiyaç duyduğu her şey. Bölüm menüsü yalnızca
     * "yan menülü" şablonda hesaplanır — diğer iki şablonda o sorguların
     * sonucu hiç kullanılmayacağı için hiç atılmaz.
     *
     * @return array<string, mixed>
     */
    public function viewData(Page $page): array
    {
        return [
            'page' => $page,
            'ancestors' => $this->ancestors($page),
            'section' => $page->template === 'sidebar' ? $this->sectionNavigation($page) : null,
        ];
    }

    /**
     * "Yan menülü" şablonun bölüm listesi. Sayfanın alt sayfaları varsa bölüm
     * başlığı sayfanın kendisi ve liste çocukları olur; yoksa bir üst sayfa
     * başlık, kardeşler liste olur. Kök seviyedeki çocuksuz bir sayfada
     * gösterilecek bir bölüm yok.
     *
     * @return array{title: string, url: string|null, items: Collection<int, Page>}|null
     */
    public function sectionNavigation(Page $page): ?array
    {
        $children = $this->visibleChildrenOf($page->id);

        if ($children->isNotEmpty()) {
            return ['title' => $page->title, 'url' => null, 'items' => $children];
        }

        $parent = $page->parent_id ? Page::visible()->whereKey($page->parent_id)->first(['id', 'title', 'path']) : null;

        if (! $parent) {
            return null;
        }

        return [
            'title' => $parent->title,
            'url' => $parent->url(),
            'items' => $this->visibleChildrenOf($parent->id),
        ];
    }

    /** @return Collection<int, Page> */
    private function visibleChildrenOf(?int $parentId): Collection
    {
        return Page::visible()
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->get(['id', 'title', 'path']);
    }

    protected function reorderModel(): string
    {
        return Page::class;
    }

    /* --------------------------------------------------------------------
     | Yol çözümlemesi
     * -------------------------------------------------------------------- */

    /**
     * Sayfanın ve altındaki her şeyin yolunu yeniden yazar. Alt ağaç kademe
     * kademe inilerek güncellenir; her kayıt Eloquent üzerinden kaydedilir ki
     * LogsActivity URL değişimini görsün (aynı istekte olduklarından log
     * ekranında tek bir işlem olarak gruplanırlar).
     */
    private function rewriteSubtree(Page $page): void
    {
        foreach ($page->children()->get() as $child) {
            $path = "{$page->path}/{$child->slug}";

            if ($child->path !== $path) {
                $child->update(['path' => $path]);
            }

            $this->rewriteSubtree($child);
        }
    }

    /**
     * Silinen sayfanın çocuklarını bir üst seviyeye taşır. Yeni seviyede
     * slug çakışması olabilir (örn. kökte zaten aynı adlı bir sayfa var),
     * o yüzden slug yeniden benzersizleştirilir.
     */
    private function promoteChildren(Page $page): void
    {
        $newParent = $page->parent;

        foreach ($page->children()->get() as $child) {
            $slug = $this->uniqueSlug($child->slug, $newParent?->id, $child->id);

            $child->update([
                'parent_id' => $newParent?->id,
                'slug' => $slug,
                'path' => $this->resolvePath($newParent, $slug),
            ]);

            $this->rewriteSubtree($child);
        }
    }

    private function resolvePath(?Page $parent, string $slug): string
    {
        return $parent ? "{$parent->path}/{$slug}" : $slug;
    }

    /**
     * Slug kardeşler arasında benzersizdir, tablo genelinde değil:
     * /kurumsal/ekip ile /hizmetler-hakkinda/ekip bir arada durabilir.
     * Yol benzersizliği buradan kendiliğinden çıkar — üst yol zaten tekil.
     */
    private function uniqueSlug(string $source, ?int $parentId, ?int $ignoreId = null): string
    {
        $base = Str::slug($source, '-', 'tr') ?: 'sayfa';
        $slug = $base;
        $suffix = 1;

        while ($this->slugTaken($slug, $parentId, $ignoreId)) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    private function slugTaken(string $slug, ?int $parentId, ?int $ignoreId): bool
    {
        return Page::query()
            ->where('slug', $slug)
            ->when($parentId, fn ($query, $id) => $query->where('parent_id', $id), fn ($query) => $query->whereNull('parent_id'))
            ->when($ignoreId, fn ($query, $id) => $query->whereKeyNot($id))
            ->exists();
    }

    private function parent(array $data): ?Page
    {
        $id = $data['parent_id'] ?? null;

        return $id ? Page::find($id) : null;
    }

    private function slugSource(array $data): string
    {
        return ($data['slug'] ?? null) ?: $data['title'];
    }

    /** `sort_order` burada yok: formda girilmez, HasSortOrder verir, sıralama modu değiştirir. */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'template' => $data['template'] ?? 'default',
            'status' => $data['status'] ?? Page::STATUS_DRAFT,
            'published_at' => $data['published_at'] ?? null,
        ];
    }

    /** Paylaşılan bileşenlerin kaydı: kapak görseli, etiketler, SEO, SSS. */
    private function syncRelations(Page $page, array $data): void
    {
        $page->syncMedia($data['cover_media_id'] ?? null, 'cover');
        $page->syncTags($data['tags'] ?? []);
        $page->syncSeo($data['seo'] ?? []);
        $page->syncFaqs($data['faqs'] ?? []);
    }
}
