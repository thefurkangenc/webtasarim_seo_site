<?php

namespace App\Models\Service;

use App\Contracts\LinksToPublicPage;
use App\Contracts\RedirectsOnMove;
use App\Contracts\SubmitsToIndexNow;
use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasRevisions;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\LogsActivity;
use App\Models\ServiceRegion\ServiceRegion;
use App\Models\User;
use App\Support\Placeholder;
use App\Support\Tree;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Hizmet. İçerik bir kez yazılır, bağlı olduğu her hizmet bölgesi için
 * yer tutucular çözülerek yeniden üretilir (bkz. renderFor()).
 */
#[Fillable(['user_id', 'title', 'slug', 'excerpt', 'content', 'status', 'sort_order'])]
class Service extends Model implements LinksToPublicPage, RedirectsOnMove, SubmitsToIndexNow
{
    use HasFaqs, HasMedia, HasRevisions, HasSeo, HasSortOrder, HasTags, LogsActivity;

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

    /**
     * Hizmetin gerçekten sayfa ürettiği bölgeler. Formda yalnızca il seçilir
     * (`regions` pivotu illeri tutar); seçili ilin altındaki her aktif bölge —
     * ilçe, mahalle, daha derini — kendiliğinden kapsanır. İle sonradan
     * eklenen ilçe hizmeti düzenlemeden sayfa olur.
     *
     * Pasif bölge altıyla birlikte düşer: pasif ilçenin aktif mahallesinin
     * adresinde pasif ilçenin adı geçerdi. Sıra ağaç sırasıdır (il, ilçeleri,
     * sonraki il...). Aynı nesnede bir kez sorgulanır.
     *
     * @return Collection<int, ServiceRegion>
     */
    public function coveredRegions(): Collection
    {
        return once(function () {
            $roots = $this->regions->where('is_active', true)->pluck('slug_path')->filter();

            if ($roots->isEmpty()) {
                return collect();
            }

            $tree = ServiceRegion::query()
                ->where(fn ($query) => $roots->each(fn (string $path) => $query
                    ->orWhere('slug_path', $path)
                    ->orWhere('slug_path', 'like', str_replace(['%', '_'], ['\\%', '\\_'], $path).'/%')))
                ->orderBy('sort_order')->orderBy('name')
                ->get();

            $inactive = $tree->where('is_active', false)->pluck('slug_path');
            $active = $tree->filter(fn (ServiceRegion $region) => $region->is_active
                && ! $inactive->contains(fn (string $path) => str_starts_with($region->slug_path, "{$path}/")))
                ->keyBy('id');

            // Tree::options kökten aşağı gezer; pasif ebeveyni düşen kayıt
            // zaten yukarıda elendiği için ağaçtan kopuk öğe kalmaz.
            return collect(array_keys(Tree::options($active)))->map(fn (int $id) => $active[$id])->values();
        });
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Revizyona hizmetin bölge bağları da girer. */
    protected function revisionExtra(): array
    {
        return ['regions' => $this->regions()->pluck('service_regions.id')->all()];
    }

    /** @param array<string, mixed> $extra */
    protected function revisionExtraPayload(array $extra): array
    {
        return ['service_regions' => $extra['regions'] ?? []];
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
            // Liste bölgeleri yüklüyse (admin datatable) üretilen sayfa sayısı da gelir.
            'region_pages_count' => $this->relationLoaded('regions') ? $this->coveredRegions()->count() : null,
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
            'seo' => $this->qualifySeo(Placeholder::replaceAll($this->seoMeta(), $values), $region),
        ];
    }

    /**
     * Meta başlık/açıklamada yer tutucu kullanılmamışsa bütün bölge sayfaları
     * birebir aynı metayla çıkar — arama motoru için bu kopya içeriktir. Bölge
     * zincirinden hiçbir ad metinde geçmiyorsa bölge adı başa eklenir.
     *
     * Zincirin herhangi bir parçası geçiyorsa dokunulmaz: editör {{city}}
     * yazmışsa "Gaziantep Web Tasarım" zaten ayrışmıştır, ilçe sayfasında
     * başa bir de "Gaziantep Şahinbey" eklemek metni bozar.
     *
     * @param  array<string, mixed>  $seo
     * @return array<string, mixed>
     */
    private function qualifySeo(array $seo, ServiceRegion $region): array
    {
        $names = $region->ancestorsAndSelf()->pluck('name');
        $mentions = fn (?string $text) => filled($text)
            && $names->contains(fn (string $name) => Str::contains($text, $name, ignoreCase: true));

        $regionName = $region->placeholders()['region'];

        if (filled($seo['title']) && ! $mentions($seo['title'])) {
            $seo['title'] = "{$regionName} {$seo['title']}";
        }

        if (filled($seo['description']) && ! $mentions($seo['description'])) {
            $seo['description'] = rtrim($seo['description'], ' .').'';
        }

        return $seo;
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

    /**
     * Bağlı SSS'ler, içerikle aynı kuralla çözülmüş halde: bölge verilirse
     * "{{region}} web sitesi fiyatları ne kadar?" o bölgenin adını alır,
     * verilmezse (şemsiye sayfa) yer tutucu tamamen kalkar ve cümle yine
     * düzgün okunur. Soru havuzu ortak olduğu için çözüm burada yapılır —
     * kayda dokunulmaz.
     *
     * @return Collection<int, array{id: int, question: string, answer: string}>
     */
    public function renderedFaqs(?ServiceRegion $region = null): Collection
    {
        $values = $region?->placeholders();

        $resolve = function (?string $text) use ($values) {
            if ($values) {
                return (string) Placeholder::replace($text, $values);
            }

            // Yer tutucu cümlenin başındaysa geriye küçük harfle başlayan bir
            // metin kalır ("{{region}} web sitesi..." -> "web sitesi...").
            // İlk harf Türkçe kurala göre büyütülür (i -> İ, ı -> I).
            $text = (string) Placeholder::strip($text);
            $first = mb_substr($text, 0, 1);

            return match ($first) {
                '' => $text,
                'i' => 'İ'.mb_substr($text, 1),
                'ı' => 'I'.mb_substr($text, 1),
                default => mb_strtoupper($first, 'UTF-8').mb_substr($text, 1),
            };
        };

        return $this->faqs->map(fn ($faq) => [
            'id' => $faq->id,
            'question' => $resolve($faq->question),
            'answer' => $resolve($faq->answer),
        ]);
    }
}
