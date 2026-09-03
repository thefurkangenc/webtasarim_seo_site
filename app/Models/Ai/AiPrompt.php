<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name', 'key', 'system_prompt', 'user_prompt',
    'ai_provider_id', 'is_active', 'is_default',
])]
class AiPrompt extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
            'provider' => $this->provider?->name ?? 'Varsayılan sağlayıcı',
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
