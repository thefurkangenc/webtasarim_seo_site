<?php

namespace App\Models\Ai;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ai_prompt_id', 'ai_provider_id', 'user_id',
    'status', 'input', 'output', 'error', 'tokens', 'duration_ms',
])]
class AiGeneration extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'input' => 'array',
            'output' => 'array',
        ];
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(AiPrompt::class, 'ai_prompt_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'finished' => $this->isFinished(),
            'output' => $this->output,
            'error' => $this->error,
            'tokens' => $this->tokens,
            'duration_ms' => $this->duration_ms,
        ];
    }
}
