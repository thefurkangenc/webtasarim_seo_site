<?php

namespace App\Services\Ai;

use App\Models\Ai\AiPrompt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AiPromptService
{
    public function list(array $filters): LengthAwarePaginator
    {
        return AiPrompt::query()
            ->with('provider:id,name')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->when($filters['key'] ?? null, fn ($query, $key) => $query->where('key', $key))
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (AiPrompt $prompt) => $prompt->toPayload());
    }

    /** Üretim modalının şablon listesi: yalnızca ilgili modülün aktif şablonları. */
    public function forKey(string $key): Collection
    {
        return AiPrompt::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): AiPrompt
    {
        return DB::transaction(function () use ($data) {
            $prompt = AiPrompt::create($this->attributes($data));

            $this->keepSingleDefault($prompt);

            return $prompt;
        });
    }

    public function update(AiPrompt $prompt, array $data): AiPrompt
    {
        return DB::transaction(function () use ($prompt, $data) {
            $prompt->update($this->attributes($data));

            $this->keepSingleDefault($prompt);

            return $prompt;
        });
    }

    public function delete(AiPrompt $prompt): void
    {
        $prompt->delete();
    }

    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'key' => $data['key'],
            'system_prompt' => $data['system_prompt'],
            'user_prompt' => $data['user_prompt'],
            'ai_provider_id' => $data['ai_provider_id'] ?: null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_default' => (bool) ($data['is_default'] ?? false),
        ];
    }

    /** Varsayılan şablon her modül anahtarı için tektir. */
    private function keepSingleDefault(AiPrompt $prompt): void
    {
        if ($prompt->is_default) {
            AiPrompt::whereKeyNot($prompt->id)
                ->where('key', $prompt->key)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
