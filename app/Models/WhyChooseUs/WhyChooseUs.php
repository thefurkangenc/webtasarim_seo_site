<?php

namespace App\Models\WhyChooseUs;

use App\Models\Concerns\HasRevisions;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'is_active', 'sort_order'])]
class WhyChooseUs extends Model
{
    use HasRevisions, HasSortOrder, LogsActivity;

    protected $table = 'why_choose_us';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'is_active' => (bool) $this->is_active,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
