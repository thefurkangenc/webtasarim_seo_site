<?php

namespace App\Models\Faq;

use App\Models\Concerns\HasRevisions;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question', 'answer', 'is_active', 'sort_order'])]
class Faq extends Model
{
    use HasRevisions, HasSortOrder, LogsActivity;

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
            'question' => $this->question,
            'answer' => $this->answer,
        ];
    }
}
