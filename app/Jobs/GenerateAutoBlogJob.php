<?php

namespace App\Jobs;

use App\Services\AutoBlog\AutoBlogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Otomatik blog üretimini kuyrukta çalıştırır. Metin + görsel isteği
 * dakikalar sürebildiği için cron'un çağırdığı adres beklemez, işi buraya
 * bırakır; PHP/cron zaman aşımı üretimi yarıda kesmez.
 *
 * Yeniden denenmez: istekler ücretlidir ve aynı hata genelde tekrar eder.
 * Hata `failed_jobs`'a düşer, Sistem Sağlığı ekranından görülür.
 *
 * Tekilleştirme YOK: adrese kaç istek gelirse o kadar job kuyruğa girer,
 * `queue:work` tek işçiyle çalıştığı sürece sırayla işlenir. Bilinçli
 * tercih — art arda birden çok yazı üretmek isteniyor.
 */
class GenerateAutoBlogJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout;

    /** @param  array<string, mixed>  $input  keywords / title / notes */
    public function __construct(public array $input = [])
    {
        // Metin ve görsel sırayla çalışır; ikisinin HTTP süresi + kayıt payı.
        // Metin İKİ kez istenebiliyor: gövde kısa kalırsa bir genişletme turu
        // daha atılıyor. config/queue.php > retry_after bunun üzerinde kalmalı.
        $this->timeout = ((int) config('auto-blog.text.timeout') * 2)
            + (int) config('auto-blog.image.timeout')
            + 60;
    }

    public function handle(AutoBlogService $service): void
    {
        $service->generate($this->input);
    }
}
