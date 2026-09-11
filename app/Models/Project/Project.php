<?php

namespace App\Models\Project;

use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasRevisions;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\LogsActivity;
use App\Models\ProjectCategory\ProjectCategory;
use App\Models\Service\Service;
use App\Models\Testimonial\Testimonial;
use App\Models\User;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * "Neler Yaptık" kaydı — bir vaka çalışması. İçerik dışındaki üç parça
 * onu blog yazısından ayırır: künye (müşteri/sektör/tarih/adres), ölçülebilir
 * sonuçlar ve bağlı olduğu hizmetler.
 */
#[Fillable([
    'project_category_id', 'user_id', 'testimonial_id',
    'title', 'slug', 'excerpt', 'content',
    'client_name', 'sector', 'project_url', 'started_at', 'completed_at', 'duration',
    'technologies', 'results', 'video_url',
    'status', 'is_featured', 'sort_order',
])]
class Project extends Model
{
    use HasFaqs, HasMedia, HasRevisions, HasSeo, HasSortOrder, HasTags, LogsActivity;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    /** Durum seçeneklerinin tek kaynağı — form select'i ve doğrulama buradan okur. */
    public const STATUSES = [
        self::STATUS_DRAFT => 'Taslak',
        self::STATUS_PUBLISHED => 'Yayında',
    ];

    /**
     * Sonuç satırının yönü. Ön yüzde ok ikonunu ve rengi belirler —
     * "hemen çıkma oranı %60 düştü" iyi bir sonuçtur, yani yön tek başına
     * iyi/kötü demek değildir, sadece neyin olduğunu söyler.
     */
    public const DIRECTIONS = [
        'up' => 'Artış',
        'down' => 'Azalış',
        'neutral' => 'Nötr',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'results' => 'array',
            'started_at' => 'date',
            'completed_at' => 'date',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function testimonial(): BelongsTo
    {
        return $this->belongsTo(Testimonial::class);
    }

    /** Pivot tablo adı açıkça verilir; Eloquent'in türeteceği ad yanlış olur. */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'project_service');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Künyede "Mart 2026" görünür; gün bilgisi bir projede anlamsız. */
    public function completedLabel(): ?string
    {
        return $this->completed_at?->translatedFormat('F Y');
    }

    /** @return array{provider: string, id: string, embed_url: string, thumbnail: string|null}|null */
    public function videoEmbed(): ?array
    {
        return VideoEmbed::parse($this->video_url);
    }

    /**
     * Kullanılabilir sonuç satırları — etiketi boş olanlar atılır. Form boş
     * satır göndermeye izin verdiği için okuyan taraf her zaman bunu kullanır.
     *
     * @return list<array{label: string, value: string, direction: string}>
     */
    public function resultRows(): array
    {
        return collect($this->results ?? [])
            ->filter(fn ($row) => filled($row['label'] ?? null))
            ->map(fn ($row) => [
                'label' => (string) $row['label'],
                'value' => (string) ($row['value'] ?? ''),
                'direction' => array_key_exists($row['direction'] ?? '', self::DIRECTIONS)
                    ? $row['direction']
                    : 'neutral',
            ])
            ->values()
            ->all();
    }

    /** Revizyona projenin hizmet bağları da girer. */
    protected function revisionExtra(): array
    {
        return ['services' => $this->services()->pluck('services.id')->all()];
    }

    /** @param  array<string, mixed>  $extra */
    protected function revisionExtraPayload(array $extra): array
    {
        return ['services' => $extra['services'] ?? []];
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
            'is_featured' => $this->is_featured,
            'category' => $this->category?->name,
            'client_name' => $this->client_name,
            'sector' => $this->sector,
            'completed_label' => $this->completedLabel(),
            'services_count' => $this->services_count ?? 0,
            'results_count' => count($this->resultRows()),
            'gallery_count' => $this->getMedia('gallery')->count(),
            'has_video' => filled($this->video_url) || $this->getFirstMedia('video') !== null,
            'author' => $this->author?->name,
            'thumb' => $this->mediaUrl('cover', 'thumb'),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }

    /**
     * SEO analizi içeriğe ek olarak künyeyi de okur: müşteri adı ve sektör
     * proje sayfasında gerçekten basılan metindir, analizden gizlenmesi
     * skoru olduğundan düşük gösterirdi.
     *
     * @return array<string, string>
     */
    public function seoAnalysisInput(): array
    {
        return [
            'focus_keyword' => (string) ($this->seo?->focus_keyword ?? ''),
            'title' => (string) ($this->seo?->meta_title ?: $this->title),
            'description' => (string) ($this->seo?->meta_description ?: $this->excerpt),
            'slug' => (string) $this->slug,
            'content' => trim((string) $this->content.' '.$this->client_name.' '.$this->sector),
            'url_host' => (string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: ''),
            'type' => $this->seoAnalysisType(),
        ];
    }
}
