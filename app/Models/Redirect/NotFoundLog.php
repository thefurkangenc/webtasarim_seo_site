<?php

namespace App\Models\Redirect;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 404 alan bir yol. Aynı yol tekrar 404 alırsa yeni satır açılmaz, `hits`
 * artar ve son görülme bilgileri güncellenir.
 *
 * Yüksek hacimli olabildiği için denetim kaydına (LogsActivity) yazılmaz —
 * kendi tablosu var.
 */
#[Fillable([
    'path', 'hits', 'first_seen_at', 'last_seen_at',
    'last_referer', 'last_user_agent', 'last_ip', 'resolved',
])]
class NotFoundLog extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'hits' => 'integer',
            'resolved' => 'boolean',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('resolved', false);
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'hits' => $this->hits,
            'resolved' => $this->resolved,
            'last_referer' => $this->last_referer,
            'last_user_agent' => $this->last_user_agent,
            'last_ip' => $this->last_ip,
            'first_seen_at' => $this->first_seen_at?->format('d.m.Y H:i'),
            'last_seen_at' => $this->last_seen_at?->format('d.m.Y H:i'),
        ];
    }
}
