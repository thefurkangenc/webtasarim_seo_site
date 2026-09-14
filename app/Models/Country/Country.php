<?php

namespace App\Models\Country;

use App\Support\Phone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'iso2', 'dial_code', 'mask', 'flag', 'sort_order', 'is_active', 'strip_leading_zero'])]
class Country extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'strip_leading_zero' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function digitCount(): int
    {
        return Phone::digitCount($this->mask);
    }

    /** Ulusal numaradaki baştaki 0 (TR vb.) yazılırken düşürülür. */
    public function stripsLeadingZero(): bool
    {
        return (bool) $this->strip_leading_zero;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'iso2' => $this->iso2,
            'dial_code' => $this->dial_code,
            'mask' => $this->mask,
            'flag' => $this->flag,
            'digit_count' => $this->digitCount(),
            'strip_leading_zero' => $this->stripsLeadingZero(),
        ];
    }
}
