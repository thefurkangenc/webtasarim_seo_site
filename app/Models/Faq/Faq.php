<?php

namespace App\Models\Faq;

use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question', 'answer', 'sort_order'])]
class Faq extends Model
{
    use HasSortOrder, LogsActivity;

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'answer' => $this->answer,
        ];
    }
}
