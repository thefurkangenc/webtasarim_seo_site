<?php

namespace App\Models\Concerns;

use App\Support\AudienceContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Zaman aralığı + sayfa hedefi. Duyuru şeridi ve popup aynı kolonları
 * paylaşır; bu trait her ikisinde de kullanılır.
 */
trait HasAudience
{
    /** @param  Builder<static>  $query */
    public function scopeLive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    public function matchesAudience(AudienceContext $context): bool
    {
        return match ($this->audience) {
            'all' => true,
            'home' => $context->isHome,
            'selected' => $context->matchesSelected($this->page_ids, $this->blog_ids, $this->service_ids),
            default => false,
        };
    }

    public function audienceLabel(): string
    {
        return config('notices.audiences.'.$this->audience, $this->audience);
    }

    public function scheduleLabel(): string
    {
        $from = $this->starts_at instanceof Carbon ? $this->starts_at->format('d.m.Y H:i') : null;
        $to = $this->ends_at instanceof Carbon ? $this->ends_at->format('d.m.Y H:i') : null;

        if ($from && $to) {
            return $from.' — '.$to;
        }

        if ($from) {
            return $from.' tarihinden itibaren';
        }

        if ($to) {
            return $to.' tarihine kadar';
        }

        return 'Sürekli';
    }
}
