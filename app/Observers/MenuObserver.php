<?php

namespace App\Observers;

use App\Models\Menu\Menu;
use App\Models\Menu\MenuItem;
use App\Services\Menu\MenuRenderer;

/**
 * Menü ya da öğe değişince ön yüz önbelleğini temizler. AppServiceProvider'da
 * hem Menu hem MenuItem'a bağlanır.
 */
class MenuObserver
{
    public function saved(Menu|MenuItem $model): void
    {
        MenuRenderer::forget($this->keyFor($model));
    }

    public function deleted(Menu|MenuItem $model): void
    {
        MenuRenderer::forget($this->keyFor($model));
    }

    private function keyFor(Menu|MenuItem $model): ?string
    {
        return $model instanceof Menu ? $model->key : $model->menu?->key;
    }
}
