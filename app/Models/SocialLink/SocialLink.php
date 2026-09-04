<?php

namespace App\Models\SocialLink;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'url', 'sort_order'])]
class SocialLink extends Model
{
    use HasMedia, HasSortOrder;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        $icon = $this->getFirstMedia('icon');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'sort_order' => $this->sort_order,
            'icon' => $icon?->toPayload(),
        ];
    }
}
