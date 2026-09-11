<?php

namespace App\Observers;

use App\Jobs\GenerateSitemapJob;
use Illuminate\Database\Eloquent\Model;

/**
 * Sitemap'e giren bir model kaydedilince/silinince siteyi yeniden üretir.
 * `Page`, `Blog`, `Service`, `ServiceRegion` için `AppServiceProvider`'da
 * kaydedilir. 1 dakikalık gecikme + işin `ShouldBeUnique` olması, arka arkaya
 * kayıtlarda (toplu içerik girişi gibi) tek üretime düşürür.
 */
class SitemapObserver
{
    public function saved(Model $model): void
    {
        GenerateSitemapJob::dispatch()->delay(now()->addMinute());
    }

    public function deleted(Model $model): void
    {
        GenerateSitemapJob::dispatch()->delay(now()->addMinute());
    }
}
