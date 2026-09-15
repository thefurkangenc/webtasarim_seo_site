<?php

namespace App\Models\Slider;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasRevisions;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slogan', 'description', 'button_text', 'button_url', 'is_active', 'sort_order'])]
class Slider extends Model
{
    use HasMedia, HasRevisions, HasSortOrder, LogsActivity;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'is_active' => (bool) $this->is_active,
            'title' => $this->title,
            'slogan' => $this->slogan,
            'description' => $this->description,
            'button_text' => $this->button_text,
            'button_url' => $this->button_url,
            'desktop' => $this->getFirstMedia('desktop')?->toPayload(),
            'mobile' => $this->getFirstMedia('mobile')?->toPayload(),
        ];
    }
}
