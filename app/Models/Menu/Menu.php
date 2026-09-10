<?php

namespace App\Models\Menu;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Menü konumu — header ya da bir footer sütunu. Sabit yuvalardır:
 * config/menus.php'den MenuSeeder ile basılır, panelden eklenip silinmez.
 * Yalnızca `title` (ön yüzde görünen başlık) ve içindeki öğeler düzenlenir.
 */
#[Fillable(['key', 'name', 'title'])]
class Menu extends Model
{
    use LogsActivity;

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /** Yalnızca kök öğeler; alt öğeler MenuItem::children üzerinden gezilir. */
    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id');
    }

    public function locationMeta(): array
    {
        return config("menus.locations.{$this->key}", ['name' => $this->name, 'title' => $this->title]);
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'title' => $this->title,
            'items_count' => $this->items_count ?? $this->items()->count(),
        ];
    }
}
