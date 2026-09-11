<?php

namespace App\Services\Lead;

use App\Mail\Lead\LeadReply;
use App\Models\Lead\Lead;
use App\Models\User;
use App\Support\Activity;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Gelen talepler (lead) gelen kutusu: listeleme, durum/atama, iç not,
 * panelden e-posta yanıtı, toplu işlem ve CSV dışa aktarım.
 */
class LeadService
{
    /** @param  array<string, mixed>  $filters */
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Lead::query()->with('assignee:id,name');

        $this->applyFilters($query, $filters);

        return $query
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? config('leads.per_page'))
            ->through(fn (Lead $lead) => $lead->toPayload());
    }

    /** Liste ekranının ilk render'ı: özet kartlar + atama filtresi seçenekleri. */
    public function indexData(): array
    {
        return [
            'stats' => $this->stats(),
            'assignees' => User::query()->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }

    /** Üst kartlar: durum dağılımı + okunmamış + bugün. */
    public function stats(): array
    {
        $byStatus = Lead::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'statuses' => collect(config('leads.statuses'))
                ->map(fn (array $meta, string $key) => [
                    'key' => $key,
                    'label' => $meta['label'],
                    'color' => $meta['color'],
                    'icon' => $meta['icon'],
                    'total' => (int) ($byStatus[$key] ?? 0),
                ])->values()->all(),
            'unread' => Lead::query()->unread()->count(),
            'today' => Lead::query()->whereDate('created_at', today())->count(),
            'trashed' => Lead::onlyTrashed()->count(),
        ];
    }

    /** Detay modalı — açılınca kayıt okundu sayılır. */
    public function showData(Lead $lead): array
    {
        if (! $lead->isRead()) {
            $lead->forceFill(['read_at' => now()])->saveQuietly();
        }

        return [
            'lead' => $lead->load('assignee:id,name'),
            'statuses' => config('leads.statuses'),
            'users' => User::query()->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }

    /** @param  array<string, mixed>  $data */
    public function update(Lead $lead, array $data): Lead
    {
        $lead->update([
            'status' => $data['status'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        return $lead;
    }

    /** Panelden e-posta yanıtı. Gönderilemezse iş kuralı hatası fırlatır. */
    public function reply(Lead $lead, array $data): void
    {
        try {
            Mail::to($lead->email)->send(new LeadReply($lead, $data['subject'], $data['body']));
        } catch (Throwable $e) {
            throw new DomainException('Yanıt gönderilemedi: '.$e->getMessage());
        }

        $lead->forceFill([
            'replied_at' => now(),
            // Yanıtlanan bir talep "yeni" kalmasın; zaten tamamlandıysa dokunulmaz.
            'status' => $lead->status === Lead::STATUS_NEW ? Lead::STATUS_IN_PROGRESS : $lead->status,
        ])->save();

        Activity::record(
            logName: 'lead',
            event: 'replied',
            description: "Talebe e-posta ile yanıt verildi: {$lead->name}",
            subject: $lead,
            subjectLabel: $lead->name,
            properties: ['to' => $lead->email, 'subject' => $data['subject']],
        );
    }

    public function toggleRead(Lead $lead): Lead
    {
        $lead->forceFill(['read_at' => $lead->isRead() ? null : now()])->saveQuietly();

        return $lead;
    }

    public function delete(Lead $lead): void
    {
        $lead->delete();
    }

    public function restore(Lead $lead): void
    {
        $lead->restore();
    }

    /**
     * Toplu işlem. Model olayı tetiklemeyen işlemler olduğu için log kaydı
     * elle yazılır (bkz. CLAUDE.md → Log Kaydı).
     *
     * @param  list<int>  $ids
     */
    public function bulk(array $ids, string $action, ?string $status = null): int
    {
        $query = Lead::query()->withTrashed()->whereIn('id', $ids);
        $leads = $query->get();

        if ($leads->isEmpty()) {
            throw new DomainException('Seçili kayıt bulunamadı.');
        }

        $count = match ($action) {
            'read' => $this->bulkFill($leads, ['read_at' => now()]),
            'unread' => $this->bulkFill($leads, ['read_at' => null]),
            'status' => $this->bulkFill($leads, ['status' => $status]),
            'delete' => $leads->each->delete()->count(),
            'restore' => $leads->each->restore()->count(),
            default => throw new DomainException('Bilinmeyen işlem.'),
        };

        Activity::record(
            logName: 'lead',
            event: $action === 'delete' ? 'bulk_delete' : 'bulk_update',
            description: $this->bulkDescription($action, $status, $count),
            subjectLabel: 'Gelen talepler',
            properties: ['action' => $action, 'status' => $status, 'ids' => $ids],
        );

        return $count;
    }

    /**
     * CSV dışa aktarım satırları — ilk satır başlıklar. Generator olduğu için
     * binlerce kayıt belleğe toplanmaz; yanıtı controller `Csv::download()`
     * ile kurar (servis HTTP bilmez).
     *
     * @param  array<string, mixed>  $filters
     * @return \Generator<int, list<string|null>>
     */
    public function exportRows(array $filters): \Generator
    {
        yield ['Tarih', 'Kaynak', 'Ad', 'E-posta', 'Telefon', 'Konu', 'Mesaj', 'Durum', 'Atanan', 'Okundu', 'Yanıtlandı', 'Sayfa', 'IP'];

        $query = Lead::query()->with('assignee:id,name');
        $this->applyFilters($query, $filters);

        $rows = $query->orderByDesc('created_at')
            ->limit((int) config('leads.export_limit'))
            ->lazy(200);

        foreach ($rows as $lead) {
            yield [
                $lead->created_at?->format('d.m.Y H:i'),
                $lead->sourceLabel(),
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->subject,
                preg_replace('/\s+/u', ' ', (string) $lead->message),
                $lead->statusLabel(),
                $lead->assignee?->name,
                $lead->read_at?->format('d.m.Y H:i'),
                $lead->replied_at?->format('d.m.Y H:i'),
                $lead->page_url,
                $lead->ip_address,
            ];
        }
    }

    /** @param  Collection<int, Lead>  $leads */
    private function bulkFill(Collection $leads, array $attributes): int
    {
        foreach ($leads as $lead) {
            $lead->forceFill($attributes)->saveQuietly();
        }

        return $leads->count();
    }

    private function bulkDescription(string $action, ?string $status, int $count): string
    {
        return match ($action) {
            'read' => "{$count} talep okundu işaretlendi.",
            'unread' => "{$count} talep okunmadı işaretlendi.",
            'status' => "{$count} talebin durumu “".config("leads.statuses.{$status}.label", $status).'” yapıldı.',
            'delete' => "{$count} talep çöp kutusuna taşındı.",
            'restore' => "{$count} talep geri alındı.",
            default => "{$count} talep güncellendi.",
        };
    }

    /** @param  array<string, mixed>  $filters */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(
                fn (Builder $q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
            ))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['source'] ?? null, fn (Builder $q, string $source) => $q->where('source', $source))
            ->when($filters['assigned_to'] ?? null, fn (Builder $q, $id) => $id === 'none'
                ? $q->whereNull('assigned_to')
                : $q->where('assigned_to', $id))
            ->when(($filters['unread'] ?? null) === '1', fn (Builder $q) => $q->unread())
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('created_at', '<=', $to))
            // Çöp kutusu ayrı bir görünüm; varsayılan listede silinmişler yok.
            ->when(($filters['trashed'] ?? null) === '1', fn (Builder $q) => $q->onlyTrashed());
    }
}
