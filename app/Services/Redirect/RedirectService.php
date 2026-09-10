<?php

namespace App\Services\Redirect;

use App\Models\Redirect\NotFoundLog;
use App\Models\Redirect\Redirect;
use App\Support\UrlPath;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RedirectService
{
    public function list(array $filters): LengthAwarePaginator
    {
        return Redirect::query()
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($q) => $q->where('from_path', 'like', "%{$term}%")->orWhere('to_url', 'like', "%{$term}%"),
            ))
            ->when($filters['match_type'] ?? null, fn ($query, $type) => $query->where('match_type', $type))
            ->when(isset($filters['status']) && $filters['status'] !== '', fn ($query) => $query->where('is_active', (bool) $filters['status']))
            ->when($filters['source'] ?? null, fn ($query, $source) => $query->where('source', $source))
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 20)
            ->through(fn (Redirect $redirect) => $redirect->toPayload());
    }

    /** @return array<string, mixed> */
    public function stats(): array
    {
        return [
            'total' => Redirect::count(),
            'active' => Redirect::active()->count(),
            'auto' => Redirect::where('source', 'auto')->count(),
            'unresolved_404' => NotFoundLog::unresolved()->count(),
            'hits_30d' => (int) Redirect::where('last_hit_at', '>=', now()->subDays(30))->sum('hits'),
        ];
    }

    public function create(array $data): Redirect
    {
        return Redirect::create($this->attributes($data) + ['source' => 'manual']);
    }

    public function update(Redirect $redirect, array $data): Redirect
    {
        $redirect->update($this->attributes($data));

        return $redirect;
    }

    public function delete(Redirect $redirect): void
    {
        $redirect->delete();
    }

    public function toggle(Redirect $redirect): Redirect
    {
        $redirect->update(['is_active' => ! $redirect->is_active]);

        return $redirect;
    }

    /* ------------------------------------------------------------------ *
     | 404 kayıtları
     * ------------------------------------------------------------------ */

    public function notFoundList(array $filters): LengthAwarePaginator
    {
        return NotFoundLog::query()
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where('path', 'like', "%{$term}%"))
            ->when(! ($filters['include_resolved'] ?? false), fn ($query) => $query->unresolved())
            ->orderBy($filters['sort'] ?? 'last_seen_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 20)
            ->through(fn (NotFoundLog $log) => $log->toPayload());
    }

    public function deleteNotFound(NotFoundLog $log): void
    {
        $log->delete();
    }

    /* ------------------------------------------------------------------ *
     | Zincir / döngü analizi
     * ------------------------------------------------------------------ */

    /**
     * Bir (kaynak → hedef) çiftinin yönlendirme grafiğinde sorun yaratıp
     * yaratmadığını söyler. Kaydetme öncesi FormRequest ve panel uyarısı
     * bunu kullanır.
     *
     * @return array{loop: bool, chain: array<int, string>}
     *                                                      loop  → hedefi izleyince kaynağa dönülüyor (kaydetme engellenmeli)
     *                                                      chain → hedef başka bir yönlendirmenin kaynağı; buradaki yollar
     *                                                      "ara duraklar", son eleman gerçek varış. Boşsa zincir yok.
     */
    public function analyze(string $fromPath, ?string $toUrl, ?int $ignoreId = null): array
    {
        $from = UrlPath::normalize($fromPath);
        $target = UrlPath::normalize($toUrl);

        if ($target === '' || UrlPath::isExternal((string) $toUrl)) {
            return ['loop' => false, 'chain' => []];
        }

        $active = Redirect::active()
            ->where('match_type', Redirect::MATCH_EXACT)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->pluck('to_url', 'from_path')
            ->map(fn ($url) => UrlPath::normalize($url))
            ->all();

        // Yeni kayıt da grafiğin parçası — hedefi zincir başlatıyor mu?
        $active[$from] = $target;

        $chain = [];
        $seen = [$from];
        $cursor = $target;
        $depth = (int) config('redirects.max_chain_depth', 10);

        while ($depth-- > 0 && isset($active[$cursor])) {
            if (in_array($cursor, $seen, true)) {
                return ['loop' => true, 'chain' => $chain];
            }

            $seen[] = $cursor;
            $chain[] = $cursor;
            $cursor = $active[$cursor];
        }

        if ($cursor === $from) {
            return ['loop' => true, 'chain' => $chain];
        }

        // $chain doluysa: hedef başka bir kuralın kaynağı. Son varış $cursor.
        return ['loop' => false, 'chain' => $chain === [] ? [] : [...$chain, $cursor]];
    }

    /* ------------------------------------------------------------------ *
     | Otomatik 301 (RedirectObserver çağırır)
     * ------------------------------------------------------------------ */

    /**
     * Bir kaydın adresi eskiden yeniye değişti. Eski adres → yeni adres 301'i
     * oluşturur; ayrıca:
     *   - hedefi eski adres olan kayıtları yeni adrese kaydırır (zincir düzleştirme)
     *   - yeni adresten eski adrese giden bir kayıt varsa (kayıt geri taşındı)
     *     onu siler (döngü koruması)
     */
    public function autoRedirect(string $fromPath, string $toPath): void
    {
        $from = UrlPath::normalize($fromPath);
        $to = UrlPath::normalize($toPath);

        if ($from === '' || $to === '' || $from === $to) {
            return;
        }

        DB::transaction(function () use ($from, $to) {
            // Kayıt eski konumuna geri döndü: from == mevcut bir kaydın to'su.
            Redirect::where('match_type', Redirect::MATCH_EXACT)
                ->where('from_path', $to)
                ->get()
                ->each(fn (Redirect $r) => UrlPath::normalize($r->to_url) === $from ? $r->delete() : null);

            // Zincir düzleştirme: A→B iken B, C'ye taşındıysa A→C yap.
            Redirect::where('match_type', Redirect::MATCH_EXACT)
                ->get()
                ->each(function (Redirect $r) use ($from, $to) {
                    if (UrlPath::normalize($r->to_url) === $from && $r->from_path !== $to) {
                        $r->update(['to_url' => '/'.$to]);
                    }
                });

            Redirect::updateOrCreate(
                ['from_path' => $from],
                [
                    'match_type' => Redirect::MATCH_EXACT,
                    'to_url' => '/'.$to,
                    'status_code' => 301,
                    'is_active' => true,
                    'source' => 'auto',
                ],
            );
        });

        // Bu yol daha önce 404 alıp loglandıysa artık çözüldü.
        NotFoundLog::where('path', $from)->update(['resolved' => true]);
    }

    /* ------------------------------------------------------------------ *
     | CSV
     * ------------------------------------------------------------------ */

    public const CSV_HEADER = ['from_path', 'match_type', 'to_url', 'status_code', 'is_active'];

    /** @return iterable<array<int, string>> */
    public function exportRows(): iterable
    {
        yield self::CSV_HEADER;

        foreach (Redirect::orderBy('from_path')->cursor() as $redirect) {
            yield [
                $redirect->from_path,
                $redirect->match_type,
                (string) $redirect->to_url,
                (string) $redirect->status_code,
                $redirect->is_active ? '1' : '0',
            ];
        }
    }

    /**
     * @param  array<int, array<string, string>>  $rows  başlık satırı ayrıştırılmış
     * @return array{created: int, updated: int, skipped: int, errors: array<int, string>}
     */
    public function import(array $rows): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // başlık + 1

            $from = UrlPath::normalize($row['from_path'] ?? '');
            $type = $row['match_type'] ?? 'exact';
            $to = trim($row['to_url'] ?? '');
            $code = (int) ($row['status_code'] ?? 301);

            if ($from === '') {
                $result['errors'][] = "Satır {$line}: kaynak adres boş.";
                $result['skipped']++;

                continue;
            }

            if (! array_key_exists($type, config('redirects.match_types'))) {
                $result['errors'][] = "Satır {$line}: geçersiz eşleşme tipi \"{$type}\".";
                $result['skipped']++;

                continue;
            }

            if (! array_key_exists($code, config('redirects.status_codes'))) {
                $result['errors'][] = "Satır {$line}: geçersiz durum kodu \"{$code}\".";
                $result['skipped']++;

                continue;
            }

            if ($code !== 410 && $to === '') {
                $result['errors'][] = "Satır {$line}: hedef boş.";
                $result['skipped']++;

                continue;
            }

            $existing = Redirect::where('from_path', $from)->first();

            Redirect::updateOrCreate(['from_path' => $from], [
                'match_type' => $type,
                'to_url' => $to ?: null,
                'status_code' => $code,
                'is_active' => ($row['is_active'] ?? '1') !== '0',
                'source' => 'import',
            ]);

            $existing ? $result['updated']++ : $result['created']++;
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public function formData(?Redirect $redirect): array
    {
        return [
            'redirect' => $redirect,
            'matchTypes' => config('redirects.match_types'),
            'statusCodes' => config('redirects.status_codes'),
        ];
    }

    /** @return array<string, mixed> */
    private function attributes(array $data): array
    {
        $isGone = (int) $data['status_code'] === 410;

        return [
            'from_path' => UrlPath::normalize($data['from_path']),
            'match_type' => $data['match_type'],
            'to_url' => $isGone ? null : $this->normalizeTarget($data['to_url'] ?? '', $data['match_type']),
            'status_code' => (int) $data['status_code'],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'notes' => $data['notes'] ?? null,
        ];
    }

    /** İç yolları baş eğik çizgiyle saklar; regex/dış URL dokunulmaz. */
    private function normalizeTarget(string $target, string $matchType): string
    {
        $target = trim($target);

        if ($matchType === Redirect::MATCH_REGEX || UrlPath::isExternal($target)) {
            return $target;
        }

        return '/'.ltrim($target, '/');
    }
}
