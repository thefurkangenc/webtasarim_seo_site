<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Yeni kayda otomatik sıra numarası verir; alan formdan kaldırılır, sadece
 * sürükle-bırak sıralama modu değiştirir.
 *
 *   class BlogCategory extends Model { use HasSortOrder; }
 *
 * Sıralama tüm tabloda tektir. Bir üst kayda göre kapsamlanması gerekiyorsa
 * (örn. klasör içindeki öğeler) modelde sortOrderScope() ezilir:
 *
 *   protected function sortOrderScope(Builder $query): Builder
 *   {
 *       return $query->where('folder_id', $this->folder_id);
 *   }
 */
trait HasSortOrder
{
    protected static function bootHasSortOrder(): void
    {
        static::creating(function ($model) {
            if (blank($model->sort_order)) {
                $model->sort_order = $model->nextSortOrder();
            }
        });
    }

    protected function nextSortOrder(): int
    {
        return ((int) $this->sortOrderScope(static::query())->max('sort_order')) + 1;
    }

    protected function sortOrderScope(Builder $query): Builder
    {
        return $query;
    }
}
