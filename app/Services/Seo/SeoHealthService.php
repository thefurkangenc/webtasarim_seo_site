<?php

namespace App\Services\Seo;

use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Service\Service;
use Illuminate\Support\Collection;

/**
 * "SEO Sağlığı" ekranının verisi. HasSeo kullanan içerik modellerini (Blog,
 * Hizmet, Sayfa) tek bir listede toplar; sekmeler bu liste üzerinde PHP'de
 * filtrelenir (içerik sayısı düşük olduğu için sorun değil).
 */
class SeoHealthService
{
    /** @var array<class-string, array{label: string, route: string, key: string}> */
    private const SOURCES = [
        Blog::class => ['label' => 'Blog', 'route' => 'admin.blog.edit', 'key' => 'blog'],
        Service::class => ['label' => 'Hizmet', 'route' => 'admin.service.edit', 'key' => 'service'],
        Page::class => ['label' => 'Sayfa', 'route' => 'admin.page.edit', 'key' => 'page'],
    ];

    public const TABS = [
        'low_score' => 'Düşük skorlu içerik',
        'no_keyword' => 'Odak kelime yok',
        'missing_meta' => 'Meta başlık/açıklama eksik',
        'missing_alt' => 'Alt metni eksik görsel',
        'duplicate_title' => 'Yinelenen meta başlık',
        'low_readability' => 'Düşük okunabilirlik',
    ];

    /**
     * @return array{total: int, analyzed: int, average: int|null, distribution: array<string, int>, tabs: array<string, int>}
     */
    public function overview(): array
    {
        $rows = $this->all();
        $analyzed = $rows->whereNotNull('score');

        $distribution = ['good' => 0, 'ok' => 0, 'bad' => 0, 'none' => 0];

        foreach ($rows as $row) {
            $distribution[$row['grade'] ?? 'none']++;
        }

        $tabCounts = [];

        foreach (array_keys(self::TABS) as $tab) {
            $tabCounts[$tab] = $this->filter($rows, $tab)->count();
        }

        return [
            'total' => $rows->count(),
            'analyzed' => $analyzed->count(),
            'average' => $analyzed->isEmpty() ? null : (int) round($analyzed->avg('score')),
            'distribution' => $distribution,
            'tabs' => $tabCounts,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function report(string $tab): array
    {
        $rows = $this->filter($this->all(), $tab);

        return $rows
            ->sortBy(fn ($r) => $r['score'] ?? -1)
            ->values()
            ->map(fn ($r) => [
                'type' => $r['type'],
                'title' => $r['title'],
                'score' => $r['score'],
                'grade' => $r['grade'],
                'readability' => $r['readability'],
                'keyword' => $r['keyword'],
                'issue' => $this->issueText($r, $tab),
                'edit_url' => $r['edit_url'],
            ])
            ->all();
    }

    /** Skoru olmayan (ya da tümünün) yeniden hesaplanması. */
    public function rescore(): int
    {
        $count = 0;

        foreach (self::SOURCES as $class => $meta) {
            $class::query()->with('seo')->chunk(100, function (Collection $models) use (&$count) {
                foreach ($models as $model) {
                    if ($model->seo) {
                        $model->refreshSeoScore();
                        $count++;
                    }
                }
            });
        }

        return $count;
    }

    /**
     * Tüm içerik kayıtlarının düzleştirilmiş SEO durumu.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function all(): Collection
    {
        $rows = collect();

        foreach (self::SOURCES as $class => $meta) {
            $class::query()->with('seo')->get()->each(function ($model) use ($rows, $meta) {
                $seo = $model->seo;
                $title = $seo?->meta_title ?: ($model->title ?? $model->name ?? '—');
                $checks = collect($seo?->score_checks ?? [])->keyBy('id');

                $rows->push([
                    'type' => $meta['label'],
                    'title' => $title,
                    'effective_title' => mb_strtolower(trim($title)),
                    'score' => $seo?->seo_score,
                    'grade' => $seo?->scoreGrade(),
                    'readability' => $seo?->readability_score,
                    'keyword' => $seo?->focus_keyword,
                    'has_meta_title' => filled($seo?->meta_title),
                    'has_meta_description' => filled($seo?->meta_description),
                    'alt_status' => $checks['images_have_alt']['status'] ?? null,
                    'edit_url' => route($meta['route'], $model),
                ]);
            });
        }

        return $rows;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function filter(Collection $rows, string $tab): Collection
    {
        return match ($tab) {
            'low_score' => $rows->filter(fn ($r) => $r['score'] === null || in_array($r['grade'], ['bad', 'ok'], true)),
            'no_keyword' => $rows->filter(fn ($r) => blank($r['keyword'])),
            'missing_meta' => $rows->filter(fn ($r) => ! $r['has_meta_title'] || ! $r['has_meta_description']),
            'missing_alt' => $rows->filter(fn ($r) => in_array($r['alt_status'], ['bad', 'ok'], true)),
            'low_readability' => $rows->filter(fn ($r) => $r['readability'] !== null && $r['readability'] < (int) config('seo.readability.ok')),
            'duplicate_title' => $this->duplicates($rows),
            default => collect(),
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function duplicates(Collection $rows): Collection
    {
        $dupes = $rows
            ->filter(fn ($r) => $r['has_meta_title'])
            ->groupBy('effective_title')
            ->filter(fn ($group) => $group->count() > 1)
            ->keys();

        return $rows->filter(fn ($r) => $r['has_meta_title'] && $dupes->contains($r['effective_title']))->values();
    }

    /** @param  array<string, mixed>  $row */
    private function issueText(array $row, string $tab): string
    {
        return match ($tab) {
            'low_score' => $row['score'] === null ? 'Henüz analiz edilmedi' : "Skor {$row['score']}/100",
            'no_keyword' => 'Odak anahtar kelime girilmemiş',
            'missing_meta' => match (true) {
                ! $row['has_meta_title'] && ! $row['has_meta_description'] => 'Meta başlık ve açıklama boş',
                ! $row['has_meta_title'] => 'Meta başlık boş',
                default => 'Meta açıklama boş',
            },
            'missing_alt' => 'Bazı görsellerde alt metni yok',
            'duplicate_title' => "Aynı meta başlık: “{$row['title']}”",
            'low_readability' => "Okunabilirlik {$row['readability']}/100",
            default => '',
        };
    }
}
