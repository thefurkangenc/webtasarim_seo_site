<?php

namespace App\Jobs;

use App\Services\IndexNow\IndexNowService;
use DomainException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Adresleri IndexNow'a bildirir. İçerik kaydedilince (IndexNowObserver) ya da
 * panelden elle tetiklenir; kuyrukta çalışır, böylece kaydetme isteği
 * arama motoru yanıtını beklemez.
 */
class SubmitToIndexNowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /** @param  list<string>  $urls */
    public function __construct(
        private readonly array $urls,
        private readonly string $source = 'otomatik',
    ) {}

    public function handle(IndexNowService $indexNow): void
    {
        try {
            $indexNow->submit($this->urls, $this->source);
        } catch (DomainException) {
            // IndexNow kapatılmış ya da adres geçersiz: kuyruğu hataya
            // düşürmenin faydası yok, işlem sessizce atlanır.
        }
    }
}
