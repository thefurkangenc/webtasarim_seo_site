<?php

namespace App\Services\Dashboard;

use App\Models\ActivityLog\ActivityLog;
use App\Models\Blog\Blog;
use App\Models\BrokenLink\BrokenLink;
use App\Models\Faq\Faq;
use App\Models\Lead\Lead;
use App\Models\Media\Media;
use App\Models\Page\Page;
use App\Models\Project\Project;
use App\Models\Redirect\NotFoundLog;
use App\Models\Seo\Seo;
use App\Models\Service\Service;
use App\Models\Subscriber\Subscriber;
use App\Models\Testimonial\Testimonial;
use App\Services\Health\HealthService;
use App\Services\Seo\SeoHealthService;
use App\Support\ModuleRegistry;

/**
 * Dashboard'un GA4 DIŞINDAKİ tüm verisi.
 *
 * GA4 tarafı ayrı durur (AnalyticsService) çünkü ağa çıkar, yavaştır ve
 * bağlantı kurulmamış olabilir — sayfa onu beklemeden açılır, grafikler
 * sonradan AJAX ile dolar. Buradaki her şey kendi veritabanımızdan gelen
 * indeksli sayımlardır, sunucu render'ında basılır.
 */
class DashboardService
{
    public function __construct(
        private readonly HealthService $health,
        private readonly SeoHealthService $seo,
        private readonly ModuleRegistry $modules,
    ) {}

    /**
     * Üstteki dört büyük sayı. Her biri bir önceki eş döneme kıyaslanır —
     * "şu an 12 abone var" tek başına bir şey söylemez, "bu hafta 5 arttı" söyler.
     *
     * @return list<array<string, mixed>>
     */
    public function counters(): array
    {
        $weekAgo = now()->subDays(7);
        $twoWeeksAgo = now()->subDays(14);

        $leadsThisWeek = Lead::where('created_at', '>=', $weekAgo)->count();
        $leadsLastWeek = Lead::whereBetween('created_at', [$twoWeeksAgo, $weekAgo])->count();

        $subsThisWeek = Subscriber::where('created_at', '>=', $weekAgo)->count();
        $subsLastWeek = Subscriber::whereBetween('created_at', [$twoWeeksAgo, $weekAgo])->count();

        $seoOverview = $this->seo->overview();

        return [
            [
                'key' => 'leads',
                'label' => 'Gelen Talep',
                'hint' => 'son 7 gün',
                'value' => $leadsThisWeek,
                'change' => $this->change($leadsThisWeek, $leadsLastWeek),
                'icon' => 'inbox',
                'tone' => 'primary',
                'route' => route('admin.lead.index'),
                'permission' => 'lead.index',
                'badge' => Lead::unread()->count(),
                'badge_label' => 'okunmamış',
            ],
            [
                'key' => 'subscribers',
                'label' => 'Bülten Abonesi',
                'hint' => 'son 7 günde yeni',
                'value' => $subsThisWeek,
                'change' => $this->change($subsThisWeek, $subsLastWeek),
                'icon' => 'mail',
                'tone' => 'success',
                'route' => route('admin.subscriber.index'),
                'permission' => 'subscriber.index',
                'badge' => Subscriber::count(),
                'badge_label' => 'toplam',
            ],
            [
                'key' => 'content',
                'label' => 'Yayında İçerik',
                'hint' => 'sayfa + yazı + hizmet + proje',
                'value' => $this->publishedCount(),
                'change' => null,
                'icon' => 'layers',
                'tone' => 'info',
                'route' => route('admin.page.index'),
                'permission' => 'page.index',
                'badge' => $this->draftCount(),
                'badge_label' => 'taslak',
            ],
            [
                'key' => 'seo',
                'label' => 'Ortalama SEO Skoru',
                'hint' => $seoOverview['analyzed'].'/'.$seoOverview['total'].' içerik analizli',
                'value' => $seoOverview['average'],
                'suffix' => '/100',
                'change' => null,
                'icon' => 'travel_explore',
                'tone' => $this->scoreTone($seoOverview['average']),
                'route' => route('admin.seo.index'),
                'permission' => 'seo.index',
                'badge' => $seoOverview['distribution']['bad'] + $seoOverview['distribution']['none'],
                'badge_label' => 'zayıf',
            ],
        ];
    }

    /**
     * "Şimdi ilgilenmen gereken şeyler." Sorun yoksa dizi boş döner ve
     * dashboard o bölümü hiç basmaz — her şey yolundayken yeşil kutularla
     * yer doldurmak uyarıları görünmez kılar.
     *
     * @return list<array<string, mixed>>
     */
    public function alerts(): array
    {
        $alerts = [];
        $report = $this->health->cached();

        if ($report && $report['counts']['critical'] > 0) {
            $alerts[] = [
                'tone' => 'danger',
                'icon' => 'error',
                'title' => $report['counts']['critical'].' kritik sistem sorunu',
                'body' => collect($report['checks'])->where('status', 'critical')->pluck('label')->join(', '),
                'route' => route('admin.health.index'),
                'permission' => 'health.index',
                'action' => 'Sistem sağlığı',
            ];
        } elseif ($report && $report['counts']['warning'] > 0) {
            $alerts[] = [
                'tone' => 'warning',
                'icon' => 'warning',
                'title' => $report['counts']['warning'].' sistem uyarısı',
                'body' => collect($report['checks'])->where('status', 'warning')->pluck('label')->join(', '),
                'route' => route('admin.health.index'),
                'permission' => 'health.index',
                'action' => 'İncele',
            ];
        }

        if ($unread = Lead::unread()->count()) {
            $alerts[] = [
                'tone' => 'primary',
                'icon' => 'mark_email_unread',
                'title' => $unread.' okunmamış talep',
                'body' => 'Siteden gelen mesajlar yanıt bekliyor.',
                'route' => route('admin.lead.index'),
                'permission' => 'lead.index',
                'action' => 'Gelen kutusu',
            ];
        }

        if ($broken = BrokenLink::visible()->count()) {
            $alerts[] = [
                'tone' => 'warning',
                'icon' => 'link_off',
                'title' => $broken.' kırık bağlantı',
                'body' => 'İçeriklerinizdeki bazı adresler çalışmıyor.',
                'route' => route('admin.broken-link.index'),
                'permission' => 'broken-link.index',
                'action' => 'Listeyi aç',
            ];
        }

        // Çözülmemiş 404: ziyaretçinin gerçekten aradığı ama bulunamayan adres.
        // Tekil bir kayıt gürültüdür; eşik bilinçli olarak biraz yukarıda.
        $notFound = NotFoundLog::where('resolved', false)->where('hits', '>=', 3)->count();

        if ($notFound > 0) {
            $alerts[] = [
                'tone' => 'info',
                'icon' => 'search_off',
                'title' => $notFound.' adres tekrar tekrar 404 veriyor',
                'body' => 'Bu adreslere yönlendirme eklemek trafiği kurtarır.',
                'route' => route('admin.redirect.index'),
                'permission' => 'redirect.index',
                'action' => 'Yönlendirmeler',
            ];
        }

        return $alerts;
    }

    /**
     * İçerik envanteri — modül başına toplam ve yayında sayısı.
     *
     * @return list<array<string, mixed>>
     */
    public function content(): array
    {
        return collect([
            $this->contentRow('Sayfalar', 'description', Page::class, 'page.index', route('admin.page.index'), 'status', Page::STATUS_PUBLISHED, 'page'),
            $this->contentRow('Blog Yazıları', 'article', Blog::class, 'blog.index', route('admin.blog.index'), 'status', Blog::STATUS_PUBLISHED, 'blog'),
            $this->contentRow('Hizmetler', 'design_services', Service::class, 'service.index', route('admin.service.index'), 'status', Service::STATUS_PUBLISHED, 'service'),
            $this->contentRow('Neler Yaptık', 'workspaces', Project::class, 'project.index', route('admin.project.index'), 'status', Project::STATUS_PUBLISHED, 'project'),
            $this->contentRow('Müşteri Yorumları', 'reviews', Testimonial::class, 'testimonial.index', route('admin.testimonial.index'), 'is_active', true, 'testimonial'),
            $this->contentRow('Sıkça Sorulan Sorular', 'quiz', Faq::class, 'faq.index', route('admin.faq.index'), 'is_active', true, 'faq'),
        ])
            ->filter(fn (array $row) => $this->modules->isActive($row['module']))
            ->values()
            ->all();
    }

    /** Medya kütüphanesi özeti — dosya sayısı ve kaplanan alan. */
    public function media(): array
    {
        $size = (int) Media::sum('size');

        return [
            'count' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'size' => Media::formatSize($size),
            'route' => route('admin.media.index'),
        ];
    }

    /**
     * Son gelen talepler — okunmamışlar vurgulanır.
     *
     * @return list<array<string, mixed>>
     */
    public function recentLeads(int $limit = 5): array
    {
        return Lead::query()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Lead $lead) => [
                ...$lead->toPayload(),
                'url' => route('admin.lead.show', $lead),
                'ago' => $lead->created_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * Son etkinlikler. Gövdesi büyük olan `properties` alanı SEÇİLMEZ —
     * dashboard'da diff gösterilmiyor, onu çekmek sayfayı gereksiz şişirir.
     *
     * @return list<array<string, mixed>>
     */
    public function recentActivity(int $limit = 8): array
    {
        return ActivityLog::query()
            ->select(['id', 'log_name', 'event', 'description', 'subject_label', 'causer_name', 'severity', 'created_at'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'description' => $log->description,
                'module' => $log->moduleMeta(),
                'event' => $log->eventMeta(),
                'causer' => $log->causer_name ?? 'Sistem',
                'subject' => $log->subject_label,
                'ago' => $log->created_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * En zayıf SEO skorları. Yalnızca ANALİZ EDİLMİŞ kayıtlar gelir: skoru
     * boş olanlar "kötü" değil, henüz ölçülmemiştir — ikisini karıştırmak
     * listeyi hiç kaydedilmemiş taslaklarla doldururdu.
     *
     * @return list<array<string, mixed>>
     */
    public function seoWeakest(int $limit = 5): array
    {
        $routes = [
            Page::class => 'admin.page.edit',
            Blog::class => 'admin.blog.edit',
            Service::class => 'admin.service.edit',
            Project::class => 'admin.project.edit',
        ];

        return Seo::query()
            ->whereNotNull('seo_score')
            ->whereIn('seoable_type', array_keys($routes))
            ->with('seoable')
            ->orderBy('seo_score')
            ->limit($limit)
            ->get()
            ->filter(fn (Seo $seo) => $seo->seoable !== null)
            ->map(fn (Seo $seo) => [
                'title' => $seo->seoable->title,
                'score' => (int) $seo->seo_score,
                'grade' => $seo->scoreGrade(),
                'focus_keyword' => $seo->focus_keyword,
                'url' => route($routes[$seo->seoable_type], $seo->seoable_id),
            ])
            ->values()
            ->all();
    }

    /**
     * Son 12 ayın aylık içerik üretimi — "ne kadar üretiyoruz" grafiği.
     * Tek sorguda gruplanır, ay ay sayım yapılmaz.
     *
     * @return array{labels: list<string>, series: list<array{name: string, data: list<int>}>}
     */
    public function productionTrend(): array
    {
        $months = collect(range(11, 0))->map(fn (int $back) => now()->subMonths($back)->startOfMonth());
        $keys = $months->map(fn ($date) => $date->format('Y-m'));

        $sources = [
            'Blog' => Blog::class,
            'Hizmet' => Service::class,
            'Proje' => Project::class,
            'Sayfa' => Page::class,
        ];

        $series = [];

        foreach ($sources as $name => $model) {
            $counts = $model::query()
                ->where('created_at', '>=', $months->first())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total")
                ->groupBy('period')
                ->pluck('total', 'period');

            $series[] = [
                'name' => $name,
                'data' => $keys->map(fn (string $key) => (int) ($counts[$key] ?? 0))->all(),
            ];
        }

        return [
            'labels' => $months->map(fn ($date) => $date->translatedFormat('M y'))->all(),
            'series' => $series,
        ];
    }

    /**
     * Gelen taleplerin durum dağılımı — halka grafik.
     *
     * @return list<array{label: string, value: int, color: string}>
     */
    public function leadStatuses(): array
    {
        $counts = Lead::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(config('leads.statuses', []))
            ->map(fn (array $meta, string $key) => [
                'label' => $meta['label'] ?? $key,
                'value' => (int) ($counts[$key] ?? 0),
                'color' => $meta['chart'] ?? '#605dff',
            ])
            ->values()
            ->all();
    }

    private function contentRow(
        string $label,
        string $icon,
        string $model,
        string $permission,
        string $route,
        string $column,
        mixed $liveValue,
        string $module,
    ): array {
        return [
            'label' => $label,
            'icon' => $icon,
            'total' => $model::count(),
            'live' => $model::where($column, $liveValue)->count(),
            'permission' => $permission,
            'route' => $route,
            'module' => $module,
        ];
    }

    private function publishedCount(): int
    {
        return Page::where('status', Page::STATUS_PUBLISHED)->count()
            + Blog::where('status', Blog::STATUS_PUBLISHED)->count()
            + Service::where('status', Service::STATUS_PUBLISHED)->count()
            + Project::where('status', Project::STATUS_PUBLISHED)->count();
    }

    private function draftCount(): int
    {
        return Page::where('status', Page::STATUS_DRAFT)->count()
            + Blog::where('status', Blog::STATUS_DRAFT)->count()
            + Service::where('status', Service::STATUS_DRAFT)->count()
            + Project::where('status', Project::STATUS_DRAFT)->count();
    }

    /** Önceki dönem sıfırsa yüzde hesaplanamaz; null "kıyas yok" demektir. */
    private function change(int $current, int $previous): ?float
    {
        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : null;
    }

    private function scoreTone(int $score): string
    {
        return match (true) {
            $score >= 71 => 'success',
            $score >= 41 => 'warning',
            default => 'danger',
        };
    }
}
