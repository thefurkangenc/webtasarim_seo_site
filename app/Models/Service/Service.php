<?php

namespace App\Models\Service;

use App\Contracts\LinksToPublicPage;
use App\Contracts\RedirectsOnMove;
use App\Contracts\SubmitsToIndexNow;
use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\LogsActivity;
use App\Models\ServiceRegion\ServiceRegion;
use App\Models\User;
use App\Support\Placeholder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Hizmet. İçerik bir kez yazılır, bağlı olduğu her hizmet bölgesi için
 * yer tutucular çözülerek yeniden üretilir (bkz. renderFor()).
 */
#[Fillable(['user_id', 'title', 'slug', 'excerpt', 'content', 'status', 'sort_order'])]
class Service extends Model implements LinksToPublicPage, RedirectsOnMove, SubmitsToIndexNow
{
    use HasFaqs, HasMedia, HasSeo, HasSortOrder, HasTags, LogsActivity;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    /** Durum seçeneklerinin tek kaynağı — form select'i ve doğrulama buradan okur. */
    public const STATUSES = [
        self::STATUS_DRAFT => 'Taslak',
        self::STATUS_PUBLISHED => 'Yayında',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Pivot tablo adı açıkça verilir; Eloquent'in türeteceği ad yanlış olur. */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(ServiceRegion::class, 'service_region_service');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function publicUrl(): ?string
    {
        return $this->status === self::STATUS_PUBLISHED
            ? route('hizmetler.show', $this->slug)
            : null;
    }

    public function indexNowUrl(): ?string
    {
        return filled($this->slug) ? route('hizmetler.show', $this->slug) : null;
    }

    public function publicLinkLabel(): string
    {
        // Menü etiketinde yer tutucu ("{{city}} Web Tasarım") anlamsız olur.
        return Placeholder::strip($this->title) ?: $this->title;
    }

    /**
     * Slug değiştiyse eski → yeni umbrella (bölgesiz) adres. Bölgeli adresler
     * ({slug}/{bölge}) önek yönlendirmesiyle kapsanır — RedirectService bunu
     * bir prefix kaydıyla birlikte oluşturur.
     *
     * @return array{from: string, to: string}|null
     */
    public function redirectableMove(): ?array
    {
        if (! $this->wasChanged('slug')) {
            return null;
        }

        return [
            'from' => 'hizmetler/'.$this->getOriginal('slug'),
            'to' => 'hizmetler/'.$this->slug,
        ];
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            ...$this->seoScorePayload(),
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'regions_count' => $this->regions_count ?? 0,
            'author' => $this->author?->name,
            'thumb' => $this->mediaUrl('cover', 'thumb'),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }

    /**
     * SEO analizi yer tutucusuz (şemsiye) içerik üzerinden yapılır —
     * "{{city}} Web Tasarım" değil "Web Tasarım".
     *
     * @return array<string, string>
     */
    public function seoAnalysisInput(): array
    {
        $generic = $this->renderGeneric();

        return [
            'focus_keyword' => (string) ($this->seo?->focus_keyword ?? ''),
            'title' => (string) ($this->seo?->meta_title ?: $generic['title']),
            'description' => (string) ($this->seo?->meta_description ?: $generic['excerpt']),
            'slug' => (string) $this->slug,
            'content' => (string) $generic['content'],
            'url_host' => (string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: ''),
            'type' => 'service',
        ];
    }

    /**
     * İçeriği verilen bölge için çözülmüş halde döndürür: metinlerdeki
     * {{region}} / {{city}} / {{district}} yer tutucuları o bölgenin
     * değerleriyle değişir. Ön yüz her (hizmet, bölge) çifti için bunu kullanır.
     *
     * @return array{title: string, excerpt: string|null, content: string|null, seo: array<string, mixed>}
     */
    public function renderFor(ServiceRegion $region): array
    {
        $values = $region->placeholders();

        return [
            'title' => Placeholder::replace($this->title, $values),
            'excerpt' => Placeholder::replace($this->excerpt, $values),
            'content' => Placeholder::replace($this->content, $values),
            'seo' => Placeholder::replaceAll($this->seoMeta(), $values),
        ];
    }

    /**
     * Bölge seçilmeden görüntülenen genel (şemsiye) sayfa için içerik.
     * renderFor()'un aksine yer tutucular bir bölgeyle değiştirilmez,
     * tamamen kaldırılır: "{{city}} Web Tasarım" -> "Web Tasarım". Başlık
     * tamamen yer tutucudan ibaretse (nadiren) ham haline düşülür — boş
     * başlıkla sayfa açılmasın.
     *
     * @return array{title: string, excerpt: string|null, content: string|null, seo: array<string, mixed>}
     */
    public function renderGeneric(): array
    {
        return [
            'title' => Placeholder::strip($this->title) ?: $this->title,
            'excerpt' => Placeholder::strip($this->excerpt),
            'content' => Placeholder::strip($this->content),
            'seo' => Placeholder::stripAll($this->seoMeta()),
        ];
    }
}
