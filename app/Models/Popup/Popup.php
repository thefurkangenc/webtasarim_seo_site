<?php

namespace App\Models\Popup;

use App\Models\Concerns\HasAudience;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title', 'heading', 'body', 'button_label', 'button_url',
    'collect_email', 'delay_seconds',
    'is_active', 'starts_at', 'ends_at', 'audience',
    'page_ids', 'blog_ids', 'service_ids',
])]
class Popup extends Model
{
    use HasAudience, HasMedia, LogsActivity;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'collect_email' => 'boolean',
            'delay_seconds' => 'integer',
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
            'heading' => $this->heading,
            'collect_email' => $this->collect_email,
            'is_active' => $this->is_active,
            'audience' => $this->audience,
            'audience_label' => $this->audienceLabel(),
            'schedule_label' => $this->scheduleLabel(),
            'image' => $this->mediaUrl('image', 'thumb'),
        ];
    }

    /** @return array<string, mixed> */
    public function toPublic(): array
    {
        return [
            'id' => $this->id,
            'heading' => $this->heading ?: $this->title,
            'body' => $this->body,
            'button_label' => $this->button_label,
            'button_url' => $this->button_url,
            'collect_email' => $this->collect_email,
            'delay_seconds' => $this->delay_seconds,
            'image' => $this->mediaUrl('image', 'medium'),
        ];
    }
}
