<?php

namespace App\Models\Lead;

use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Siteden gelen talep / mesaj. Şu an tek kaynağı iletişim formu (`source`),
 * ileride açılır pencere ve teklif formları da buraya düşecek.
 */
#[Fillable([
    'source', 'name', 'email', 'phone', 'subject', 'message', 'status',
    'assigned_to', 'read_at', 'replied_at', 'note', 'ip_address', 'user_agent', 'page_url',
])]
class Lead extends Model
{
    use LogsActivity, SoftDeletes;

    public const STATUS_NEW = 'new';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    public const STATUS_SPAM = 'spam';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function statusLabel(): string
    {
        return config("leads.statuses.{$this->status}.label", $this->status);
    }

    public function statusColor(): string
    {
        return config("leads.statuses.{$this->status}.color", 'gray');
    }

    public function sourceLabel(): string
    {
        return config("leads.sources.{$this->source}", $this->source);
    }

    /** Liste ekranı için — tam mesaj yerine kısa bir önizleme gider. */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'preview' => Str::limit(preg_replace('/\s+/u', ' ', (string) $this->message), 110),
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'status_color' => $this->statusColor(),
            'source_label' => $this->sourceLabel(),
            'assignee' => $this->assignee?->name,
            'is_read' => $this->isRead(),
            'is_replied' => $this->replied_at !== null,
            'has_note' => filled($this->note),
            'trashed' => $this->trashed(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /** Log modül anahtarı — eski 'contact' yerine kendi modülü. */
    public function activityLogName(): string
    {
        return 'lead';
    }
}
