<?php

namespace App\Services\Ai;

use App\Models\Ai\AiProvider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AiProviderService
{
    public function list(array $filters): LengthAwarePaginator
    {
        return AiProvider::query()
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('model', 'like', "%{$term}%"),
            ))
            ->when(($filters['driver'] ?? null), fn ($query, $driver) => $query->where('driver', $driver))
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (AiProvider $provider) => $provider->toPayload());
    }

    public function create(array $data): AiProvider
    {
        return DB::transaction(function () use ($data) {
            $provider = AiProvider::create($this->attributes($data));

            $this->keepSingleDefault($provider);

            return $provider;
        });
    }

    public function update(AiProvider $provider, array $data): AiProvider
    {
        return DB::transaction(function () use ($provider, $data) {
            $attributes = $this->attributes($data);

            // Anahtar alanı boş gönderildiyse mevcut anahtar korunur —
            // form kayıtlı anahtarı geri basmaz.
            if (blank($attributes['api_key'])) {
                unset($attributes['api_key']);
            }

            $provider->update($attributes);

            $this->keepSingleDefault($provider);

            return $provider;
        });
    }

    public function delete(AiProvider $provider): void
    {
        $provider->delete();
    }

    private function attributes(array $data): array
    {
        $definition = config("ai.drivers.{$data['driver']}", []);

        return [
            'name' => $data['name'],
            'driver' => $data['driver'],
            'base_url' => rtrim($data['base_url'] ?: ($definition['base_url'] ?? ''), '/'),
            'api_key' => $data['api_key'] ?? null,
            'model' => $data['model'],
            'temperature' => $data['temperature'],
            'max_tokens' => $data['max_tokens'],
            'timeout' => $data['timeout'],
            'options' => ['json_mode' => (bool) ($data['json_mode'] ?? true)],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_default' => (bool) ($data['is_default'] ?? false),
        ];
    }

    /** Varsayılan sağlayıcı tektir; yenisi işaretlenince eskisi düşer. */
    private function keepSingleDefault(AiProvider $provider): void
    {
        if ($provider->is_default) {
            AiProvider::whereKeyNot($provider->id)->where('is_default', true)->update(['is_default' => false]);
        }
    }
}
