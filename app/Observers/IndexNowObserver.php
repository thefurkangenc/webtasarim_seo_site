<?php

namespace App\Observers;

use App\Contracts\SubmitsToIndexNow;
use App\Jobs\SubmitToIndexNowJob;
use App\Services\IndexNow\IndexNowService;

/**
 * İçerik kaydedilince/silinince adresini IndexNow'a bildirir.
 * `config('indexnow.observed_models')` listesindeki modeller için
 * `AppServiceProvider`'da kaydedilir.
 *
 * Yayından çıkarılan ve silinen adres de bildirilir — motor adresi yeniden
 * tarayıp 404'ü görür ve dizininden düşürür (bkz. SubmitsToIndexNow).
 */
class IndexNowObserver
{
    public function __construct(private readonly IndexNowService $indexNow) {}

    public function saved(SubmitsToIndexNow $model): void
    {
        $this->dispatch($model);
    }

    public function deleted(SubmitsToIndexNow $model): void
    {
        $this->dispatch($model);
    }

    private function dispatch(SubmitsToIndexNow $model): void
    {
        if (! $this->indexNow->enabled() || ! $this->indexNow->autoSubmit()) {
            return;
        }

        $url = $model->indexNowUrl();

        if (blank($url)) {
            return;
        }

        // Kısa gecikme: aynı kaydetmede tetiklenen SEO/medya yazmaları bitsin.
        SubmitToIndexNowJob::dispatch([$url])->delay(now()->addSeconds(20));
    }
}
