<?php

namespace App\Observers;

use App\Contracts\RedirectsOnMove;
use App\Services\Redirect\RedirectService;
use Illuminate\Database\Eloquent\Model;

/**
 * Adresi değişen bir kayıt için otomatik 301 üretir. AppServiceProvider'da
 * config('redirects.auto_from') listesindeki her modele bağlanır.
 */
class RedirectObserver
{
    public function __construct(private readonly RedirectService $service) {}

    public function updated(Model $model): void
    {
        if (! $model instanceof RedirectsOnMove) {
            return;
        }

        $move = $model->redirectableMove();

        if ($move) {
            $this->service->autoRedirect($move['from'], $move['to']);
        }
    }
}
