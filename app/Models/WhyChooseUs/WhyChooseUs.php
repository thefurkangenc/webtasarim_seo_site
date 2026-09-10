<?php

namespace App\Models\WhyChooseUs;

use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'sort_order'])]
class WhyChooseUs extends Model
{
    use HasSortOrder, LogsActivity;

    protected $table = 'why_choose_us';

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
