<?php

namespace App\Models\Announcement;

use App\Models\Concerns\HasAudience;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title', 'message', 'button_label', 'button_url', 'tone',
    'is_active', 'starts_at', 'ends_at', 'audience',
    'page_ids', 'blog_ids', 'service_ids',
])]
class Announcement extends Model
{
    use HasAudience, LogsActivity;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'page_ids' => 'array',
            'blog_ids' => 'array',
            'service_ids' => 'array',
        ];
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'tone' => $this->tone,
            'tone_label' => config('notices.tones.'.$this->tone, $this->tone),
            'is_active' => $this->is_active,
            'audience' => $this->audience,
            'audience_label' => $this->audienceLabel(),
            'schedule_label' => $this->scheduleLabel(),
        ];
    }

    /** Ön yüze giden sade dizi — kapatma anahtarı kayıt kimliğidir. */
    public function toPublic(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'button_label' => $this->button_label,
            'button_url' => $this->button_url,
            'tone' => $this->tone,
        ];
    }
}
