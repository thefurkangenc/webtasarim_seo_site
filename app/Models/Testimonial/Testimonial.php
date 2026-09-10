<?php

namespace App\Models\Testimonial;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'title', 'content', 'rating', 'sort_order'])]
class Testimonial extends Model
{
    use HasMedia, HasSortOrder, LogsActivity;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'content' => $this->content,
            'rating' => $this->rating,
            'photo' => $this->getFirstMedia('photo')?->toPayload(),
        ];
    }
}
