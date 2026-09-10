<?php

namespace App\Models\Redirect;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Bir URL yönlendirmesi. Eşleşme üç modda çalışır (exact / prefix / regex);
 * çözümleme App\Services\Redirect\RedirectResolver'da.
 */
#[Fillable([
    'from_path', 'match_type', 'to_url', 'status_code',
    'is_active', 'source', 'notes', 'hits', 'last_hit_at',
])]
class Redirect extends Model
{
    use LogsActivity;

    public const MATCH_EXACT = 'exact';

    public const MATCH_PREFIX = 'prefix';

    public const MATCH_REGEX = 'regex';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status_code' => 'integer',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function matchTypeLabel(): string
    {
        return config("redirects.match_types.{$this->match_type}", $this->match_type);
    }

    public function statusLabel(): string
    {
        return config("redirects.status_codes.{$this->status_code}", (string) $this->status_code);
    }

    /** 410 dışındaki her kod bir hedef gerektirir. */
    public function isGone(): bool
    {
        return $this->status_code === 410;
    }

    /** Denetim kayıtlarında ham "Redirect" değil "Yönlendirmeler" görünür. */
    public function activityLogName(): string
    {
        return 'redirect';
    }

    public function activityLabel(): string
    {
        return $this->from_path;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'from_path' => $this->from_path,
            'match_type' => $this->match_type,
            'match_type_label' => $this->matchTypeLabel(),
            'to_url' => $this->to_url,
            'status_code' => $this->status_code,
            'status_label' => $this->statusLabel(),
            'is_active' => $this->is_active,
            'is_gone' => $this->isGone(),
            'source' => $this->source,
            'notes' => $this->notes,
            'hits' => $this->hits,
            'last_hit_at' => $this->last_hit_at?->format('d.m.Y H:i'),
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
