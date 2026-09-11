<?php

namespace App\Services\Subscriber;

use App\Models\Subscriber\Subscriber;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriberService
{
    /** @param  array<string, mixed>  $filters */
    public function list(array $filters): LengthAwarePaginator
    {
        return Subscriber::query()
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($query) => $query->where('email', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
            ))
            ->when(($filters['status'] ?? '') === 'active', fn ($query) => $query->active())
            ->when(($filters['status'] ?? '') === 'unsubscribed', fn ($query) => $query->whereNotNull('unsubscribed_at'))
            ->when($filters['source'] ?? null, fn ($query, $source) => $query->where('source', $source))
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 20)
            ->through(fn (Subscriber $row) => $row->toPayload());
    }

    /** @return array<string, mixed> */
    public function indexData(): array
    {
        return [
            'sources' => config('subscribers.sources'),
            'stats' => [
                'active' => Subscriber::query()->active()->count(),
                'unsubscribed' => Subscriber::query()->whereNotNull('unsubscribed_at')->count(),
            ],
        ];
    }

    public function subscribe(array $data, ?string $ip = null): ?Subscriber
    {
        if (filled($data['website'] ?? null)) {
            return null;
        }

        $existing = Subscriber::query()->where('email', $data['email'])->first();

        if ($existing) {
            if ($existing->isActive()) {
                throw new DomainException('Bu e-posta adresi zaten kayıtlı.');
            }

            $existing->forceFill([
                'name' => $data['name'] ?? $existing->name,
                'source' => $data['source'] ?? $existing->source,
                'ip' => $ip,
                'unsubscribed_at' => null,
            ])->save();

            return $existing;
        }

        return Subscriber::create([
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'source' => $data['source'] ?? 'footer',
            'ip' => $ip,
        ]);
    }

    public function unsubscribe(string $token): Subscriber
    {
        $subscriber = Subscriber::query()->where('token', $token)->firstOrFail();
        if ($subscriber->isActive()) {
            $subscriber->forceFill(['unsubscribed_at' => now()])->save();
        }

        return $subscriber;
    }

    public function delete(Subscriber $subscriber): void
    {
        $subscriber->delete();
    }

    /** @return \Generator<int, array<int, string>> */
    public function exportRows(): \Generator
    {
        yield ['E-posta', 'Ad', 'Kaynak', 'Kayıt tarihi', 'Durum'];

        $query = Subscriber::query()->orderBy('id');

        foreach ($query->cursor() as $row) {
            yield [
                $row->email,
                $row->name ?? '',
                $row->sourceLabel(),
                $row->created_at?->format('d.m.Y H:i') ?? '',
                $row->isActive() ? 'Abone' : 'Ayrıldı',
            ];
        }
    }
}
