<?php

namespace App\Models\Reference;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasRevisions;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'url', 'is_active', 'sort_order'])]
class Reference extends Model
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
            'name' => $this->name,
            'url' => $this->url,
            'sort_order' => $this->sort_order,
            'logo' => $this->getFirstMedia('logo')?->toPayload(),
        ];
    }
}
