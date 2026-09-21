<?php

namespace App\Services\Blog;

use App\Models\Blog\Blog;
use App\Models\BlogCategory\BlogCategory;
use App\Models\Tag\Tag;
use App\Support\Slug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BlogService
{
    public function list(array $filters): LengthAwarePaginator
    {
        return Blog::query()
            ->with(['category:id,name', 'author:id,name', 'media', 'seo'])
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('excerpt', 'like', "%{$term}%"),
            ))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['blog_category_id'] ?? null,
                fn ($query, $id) => $query->where('blog_category_id', $id))
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Blog $blog) => $blog->toPayload());
    }

    public function create(array $data): Blog
    {
        return DB::transaction(function () use ($data) {
            $blog = Blog::create([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['title'], 'blogs'),
                'user_id' => auth()->id(),
            ]);

            $this->syncRelations($blog, $data);

            return $blog;
        });
    }

    public function update(Blog $blog, array $data): Blog
    {
        return DB::transaction(function () use ($blog, $data) {
            $blog->update([
                ...$this->attributes($data),
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['title'], 'blogs', $blog->id),
            ]);

            $this->syncRelations($blog, $data);

            return $blog;
        });
    }

    public function delete(Blog $blog): void
    {
        DB::transaction(function () use ($blog) {
            // Medya kütüphanedeki dosyayı silmez, yalnızca bağı koparır.
            $blog->syncMedia(null, 'cover');
            $blog->tags()->detach();
            $blog->faqs()->detach();
            $blog->seo()->delete();
            $blog->delete();
        });
    }

    /**
     * Ön yüz blog listesi — yalnızca yayındaki yazılar, sayfalı. Kategori ve
     * etiket sayfaları da burayı kullanır; view ikisini de ayırt edebilsin diye
     * seçili taksonomi dönüşte geri verilir.
     */
    public function listing(?BlogCategory $category = null, ?Tag $tag = null, int $perPage = 9): array
    {
        return [
            'blogs' => Blog::where('status', Blog::STATUS_PUBLISHED)
                ->with(['media', 'author:id,name', 'category:id,name,slug'])
                ->when($category, fn ($query, BlogCategory $selected) => $query->where('blog_category_id', $selected->id))
                ->when($tag, fn ($query, Tag $selected) => $query->whereHas('tags', fn ($q) => $q->whereKey($selected->id)))
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->paginate($perPage),
            'categories' => $this->categories(),
            'category' => $category,
            'tag' => $tag,
        ];
    }

    /**
     * Liste sayfasının filtre çubuğu: yayında yazısı olan kategoriler, yazı
     * sayılarıyla. Boş kategori çubuğa girmez — tıklandığında boş sayfa açan
     * bir link SEO'da da kullanıcıda da bedava zarar.
     *
     * @return Collection<int, BlogCategory>
     */
    public function categories(): Collection
    {
        $published = fn ($query) => $query->where('status', Blog::STATUS_PUBLISHED);

        return BlogCategory::where('is_active', true)
            ->whereHas('blogs', $published)
            ->withCount(['blogs' => $published])
            ->orderBy('sort_order')
            ->get();
    }

    /** Ön yüzde slug ile kategori. Pasif kategori 404'e düşer. */
    public function findCategoryBySlug(string $slug): ?BlogCategory
    {
        return BlogCategory::where('slug', $slug)
            ->where('is_active', true)
            ->with('seo.ogMedia')
            ->first();
    }

    /**
     * Ön yüzde slug ile etiket. Etiketler modüller arası paylaşıldığı için
     * yayında blog yazısı olmayan bir etiket de eşleşebilir; o durumda sayfa
     * boş durumla açılır — etiket gerçekten var, 404 doğru cevap değil.
     */
    public function findTagBySlug(string $slug): ?Tag
    {
        return Tag::where('slug', $slug)->where('is_active', true)->first();
    }

    /**
     * Ön yüzde yayındaki yazılar — ana sayfa teaser'ı bunu kullanır.
     *
     * @return Collection<int, Blog>
     */
    public function active(?int $limit = null): Collection
    {
        return Blog::where('status', Blog::STATUS_PUBLISHED)
            ->with(['media', 'author:id,name'])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->when($limit, fn ($query, $limit) => $query->limit($limit))
            ->get();
    }

    /**
     * Ön yüzde slug ile tekil yazı. Taslak veya eşleşmeyen slug için null döner.
     */
    public function findBySlug(string $slug): ?Blog
    {
        return Blog::where('slug', $slug)
            ->where('status', Blog::STATUS_PUBLISHED)
            ->with(['media', 'author:id,name', 'tags', 'faqs', 'seo.ogMedia'])
            ->first();
    }

    /**
     * Detay sayfasının altındaki "diğer yazılar" — mevcut yazı hariç.
     *
     * @return Collection<int, Blog>
     */
    public function related(Blog $blog, int $limit = 2): Collection
    {
        return Blog::where('status', Blog::STATUS_PUBLISHED)
            ->where('id', '!=', $blog->id)
            ->with(['media', 'author:id,name'])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    private function attributes(array $data): array
    {
        $status = $data['status'] ?? Blog::STATUS_DRAFT;

        return [
            'blog_category_id' => ($data['blog_category_id'] ?? null) ?: null,
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'status' => $status,
            // Yayına alınırken tarih verilmediyse şimdiyi damgala.
            'published_at' => $status === Blog::STATUS_PUBLISHED
                ? (($data['published_at'] ?? null) ?: now())
                : (($data['published_at'] ?? null) ?: null),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
        ];
    }

    /** Paylaşılan bileşenlerin kaydı: kapak görseli, etiketler, SEO, FAQ. */
    private function syncRelations(Blog $blog, array $data): void
    {
        $blog->syncMedia($data['cover_media_id'] ?? null, 'cover');
        $blog->syncTags($data['tags'] ?? []);
        $blog->syncSeo($data['seo'] ?? []);
        $blog->syncFaqs($data['faqs'] ?? []);
    }
}
