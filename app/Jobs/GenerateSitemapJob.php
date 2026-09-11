<?php

namespace App\Jobs;

use App\Services\Sitemap\SitemapService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Site haritasını yeniden üretir. `ShouldBeUnique` sayesinde art arda içerik
 * kaydedilse (SitemapObserver) ya da "Şimdi Oluştur" birkaç kez tıklansa bile
 * kısa sürede tek üretim çalışır.
 */
class GenerateSitemapJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 300;

    public function handle(SitemapService $sitemap): void
    {
        $sitemap->generate();
    }
}
