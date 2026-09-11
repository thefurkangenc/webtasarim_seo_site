<?php

namespace App\Models\Subscriber;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['email', 'name', 'source', 'token', 'ip', 'unsubscribed_at'])]
class Subscriber extends Model
{
    use LogsActivity;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $subscriber): void {
            $subscriber->token ??= Str::lower(Str::random(48));
        });
    }

    /** @param  Builder<static>  $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('unsubscribed_at');
    }

    public function isActive(): bool
    {
        return $this->unsubscribed_at === null;
    }

    public function sourceLabel(): string
    {
        return config('subscribers.sources.'.$this->source, $this->source);
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'source' => $this->source,
            'source_label' => $this->sourceLabel(),
            'is_active' => $this->isActive(),
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
            'unsubscribed_at' => $this->unsubscribed_at?->format('d.m.Y H:i'),
        ];
    }

    public function activityLabel(): string
    {
        return $this->email;
    }
}
