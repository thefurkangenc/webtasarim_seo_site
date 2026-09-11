<?php

namespace App\Services\BrokenLink;

use App\Jobs\ScanBrokenLinksJob;
use App\Models\Blog\Blog;
use App\Models\BrokenLink\BrokenLink;
use App\Models\Hero\Hero;
use App\Models\Menu\MenuItem;
use App\Models\Page\Page;
use App\Models\Project\Project;
use App\Models\Service\Service;
use App\Support\Activity;
use App\Support\Placeholder;
use Generator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Kırık link denetimi: içeriklerden adresleri toplar, LinkChecker'a sorar ve
 * sonucu `broken_links` tablosunda biriktirir.
 *
 * Tarama tekrarlanabilir: her satır kaynak + adres çiftiyle benzersizdir,
 * ikinci taramada aynı satır güncellenir. Bu taramada hiç dokunulmayan
 * satırlar silinir — link düzeltilmiş ya da kaynak kayıt kaldırılmıştır.
 */
class BrokenLinkService
{
    /** Taranan içerik modelleri ve HTML tutan alanları. */
    private const CONTENT_SOURCES = [
        [Page::class, 'content'],
        [Blog::class, 'content'],
        [Service::class, 'content'],
        [Project::class, 'content'],
    ];

    public function __construct(
        private readonly LinkExtractor $extractor,
        private readonly LinkChecker $checker,
    ) {}

    public function list(array $filters): LengthAwarePaginator
    {
        return BrokenLink::query()
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($q) => $q->where('url', 'like', "%{$term}%")->orWhere('source_label', 'like', "%{$term}%"),
            ))
            ->when($filters['scope'] ?? null, fn ($query, $scope) => $query->where('scope', $scope))
            ->when($filters['kind'] ?? null, fn ($query, $kind) => $query->where('kind', $kind))
            ->when($filters['reason'] ?? null, fn ($query, $reason) => $query->where('reason', $reason))
            ->when($filters['source_type'] ?? null, fn ($query, $type) => $query->where('source_type', $type))
            // Yok sayılanlar bilinçli kararlardır; istenmedikçe listeye girmez.
            ->when(empty($filters['include_ignored']), fn ($query) => $query->visible())
            ->orderBy($filters['sort'] ?? 'last_checked_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 20)
            ->through(fn (BrokenLink $link) => $link->toPayload());
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        return [
            'total' => BrokenLink::visible()->count(),
            'internal' => BrokenLink::visible()->where('scope', 'internal')->count(),
            'external' => BrokenLink::visible()->where('scope', 'external')->count(),
            'ignored' => BrokenLink::where('ignored', true)->count(),
        ];
    }

    /** Liste ekranının filtre seçenekleri ve son tarama özeti. */
    public function indexData(): array
    {
        return [
            'stats' => $this->stats(),
            'sources' => config('broken-links.sources'),
            'kinds' => config('broken-links.kinds'),
            'scopes' => config('broken-links.scopes'),
            'reasons' => config('broken-links.reasons'),
            'lastScan' => $this->lastScan(),
        ];
    }

    /** @return array<string, mixed>|null */
    public function lastScan(): ?array
    {
        $scan = Cache::get('broken-links.last_scan');

        // Cache'te Carbon değil ISO metin durur (kuyruk işçisi ile web isteği
        // arasında taşınıyor); gösterimden hemen önce nesneye çevrilir.
        return $scan ? [...$scan, 'at' => Carbon::parse($scan['at'])] : null;
    }

    public function queueScan(): void
    {
        ScanBrokenLinksJob::dispatch();
    }

    /**
     * Tüm kaynakları tarar. Uzun sürebilir (dış adresler için HTTP isteği) —
     * panelden çağrıldığında kuyruğa atılır, haftalık görev de aynı yolu izler.
     *
     * @return array<string, int>
     */
    public function scan(): array
    {
        $startedAt = now();
        $checked = 0;

        foreach ($this->candidates() as $candidate) {
            $checked++;

            $result = $candidate['url'] === null
                ? $candidate['fallback']
                : $this->checker->check($candidate['url']);

            if ($result) {
                $this->record($candidate, $result, $startedAt);
            }
        }

        $removed = BrokenLink::where('last_checked_at', '<', $startedAt)->delete();
        $broken = BrokenLink::visible()->count();

        Cache::forever('broken-links.last_scan', [
            'at' => $startedAt->toIso8601String(),
            'checked' => $checked,
            'broken' => $broken,
            'removed' => $removed,
        ]);

        Activity::record(
            logName: 'broken-link',
            event: 'scan',
            description: "{$checked} adres denetlendi, {$broken} kırık link bulundu.",
            subjectLabel: 'Kırık Link Denetimi',
            properties: ['new' => ['checked' => $checked, 'broken' => $broken, 'removed' => $removed]],
        );

        return ['checked' => $checked, 'broken' => $broken, 'removed' => $removed];
    }

    public function toggleIgnore(BrokenLink $link): BrokenLink
    {
        $link->update(['ignored' => ! $link->ignored]);

        return $link;
    }

    public function delete(BrokenLink $link): void
    {
        $link->delete();
    }

    /** @return iterable<array<int, string>> */
    public function exportRows(): iterable
    {
        yield ['Adres', 'Tür', 'Kapsam', 'Sebep', 'HTTP', 'Açıklama', 'Nerede', 'Kaynak', 'Yok sayıldı', 'Son kontrol'];

        foreach (BrokenLink::orderBy('source_type')->orderBy('source_label')->cursor() as $link) {
            yield [
                $link->url,
                $link->kindLabel(),
                config("broken-links.scopes.{$link->scope}", $link->scope),
                $link->reasonLabel(),
                (string) $link->status_code,
                (string) $link->message,
                $link->source_label,
                $link->sourceTypeLabel(),
                $link->ignored ? 'Evet' : 'Hayır',
                $link->last_checked_at?->format('d.m.Y H:i') ?? '',
            ];
        }
    }

    /**
     * Taranacak adresler. Üç kaynak grubu var: içerik alanlarındaki HTML,
     * menü öğelerinin adresleri ve tanıtım alanının buton adresi.
     *
     * `url` null olan aday, adresin daha kontrol edilmeden kırık olduğunu
     * bildirir (menüde silinmiş bir kayda ya da kaldırılmış bir route'a bağlı
     * öğe gibi) — sebebi `fallback` taşır.
     *
     * @return Generator<int, array<string, mixed>>
     */
    private function candidates(): Generator
    {
        foreach (self::CONTENT_SOURCES as [$model, $field]) {
            foreach ($model::query()->select('id', 'title', $field)->cursor() as $record) {
                // Hizmet içeriği yer tutucu taşır ({{city}} gibi); ön yüzde
                // temizlenen hali taranır ki adresler gerçek çıktıyla eşleşsin.
                $html = $model === Service::class
                    ? Placeholder::strip($record->{$field})
                    : $record->{$field};

                foreach ($this->extractor->extract($html) as $link) {
                    yield $this->candidate($model, $record->id, $record->title, $field, $link['url'], $link['kind']);
                }
            }
        }

        foreach (MenuItem::with('menu:id,name')->where('status', true)->cursor() as $item) {
            $label = ($item->menu?->name ?? 'Menü').' → '.($item->label ?: $item->resolveUrl());

            yield $item->resolveUrl()
                ? $this->candidate(MenuItem::class, $item->id, $label, 'url', $item->resolveUrl(), 'link')
                : $this->unresolvedMenuItem($item, $label);
        }

        $hero = Hero::first();

        if ($hero && filled($hero->button_url)) {
            yield $this->candidate(Hero::class, $hero->id, 'Tanıtım alanı butonu', 'button_url', $hero->button_url, 'link');
        }
    }

    /** @return array<string, mixed> */
    private function candidate(string $type, int $id, ?string $label, string $field, string $url, string $kind): array
    {
        return [
            'source_type' => $type,
            'source_id' => $id,
            'source_label' => Str::limit((string) $label, 180),
            'source_field' => $field,
            'url' => Str::limit($url, 990, ''),
            'kind' => $kind,
            'fallback' => null,
        ];
    }

    /**
     * Adresi hiç üretilemeyen menü öğesi: bağlı kayıt silinmiş/yayından
     * kalkmış ya da işaret ettiği route artık yok.
     *
     * @return array<string, mixed>
     */
    private function unresolvedMenuItem(MenuItem $item, string $label): array
    {
        $isLinkable = $item->link_type === MenuItem::TYPE_LINKABLE;

        return [
            'source_type' => MenuItem::class,
            'source_id' => $item->id,
            'source_label' => Str::limit($label, 180),
            'source_field' => 'url',
            'url' => null,
            'kind' => 'link',
            'fallback' => [
                'scope' => 'internal',
                'status_code' => null,
                'reason' => $isLinkable ? 'unpublished' : 'unresolved',
                'message' => $isLinkable
                    ? 'Bağlı kayıt silinmiş ya da yayında değil; öğe menüde görünmüyor.'
                    : 'Öğenin işaret ettiği hazır bağlantı artık tanımlı değil.',
            ],
            // Kayıtta adres kolonu boş kalmasın: hangi öğe olduğu okunabilsin.
            'stored_url' => $isLinkable
                ? 'kayıt: '.class_basename((string) $item->linkable_type).' #'.$item->linkable_id
                : 'hazır bağlantı: '.$item->route_name,
        ];
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @param  array<string, mixed>  $result
     */
    private function record(array $candidate, array $result, Carbon $startedAt): void
    {
        $url = $candidate['url'] ?? $candidate['stored_url'];

        $link = BrokenLink::firstOrNew([
            'source_type' => $candidate['source_type'],
            'source_id' => $candidate['source_id'],
            'url_hash' => BrokenLink::hash($url),
        ]);

        $link->fill([
            'source_label' => $candidate['source_label'],
            'source_field' => $candidate['source_field'],
            'url' => $url,
            'kind' => $candidate['kind'],
            'scope' => $result['scope'],
            'status_code' => $result['status_code'],
            'reason' => $result['reason'],
            'message' => $result['message'],
            'first_seen_at' => $link->first_seen_at ?? $startedAt,
            'last_checked_at' => $startedAt,
        ]);

        // Tarama yüzlerce satır yazabilir; her biri için denetim kaydı
        // açılmaz — taramanın özeti scan() içinde tek kayıt olarak düşer.
        $link->saveQuietly();
    }
}
