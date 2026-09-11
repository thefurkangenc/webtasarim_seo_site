<?php

namespace App\Console\Commands;

use App\Services\BrokenLink\BrokenLinkService;
use Illuminate\Console\Command;

/**
 * Kırık link taraması. Haftalık zamanlanmıştır (bkz. bootstrap/app.php) —
 * sunucuda `schedule:run` cron'u yoksa yalnızca panelden elle tetiklenir.
 *
 *   php artisan broken-links:scan          (kuyruğa atar)
 *   php artisan broken-links:scan --sync   (beklemeden burada çalıştırır)
 */
class BrokenLinkScanCommand extends Command
{
    protected $signature = 'broken-links:scan {--sync : Kuyruğa atmadan hemen çalıştır}';

    protected $description = 'İçerik, menü ve tanıtım alanındaki adresleri denetler.';

    public function handle(BrokenLinkService $service): int
    {
        if (! $this->option('sync')) {
            $service->queueScan();
            $this->info('Tarama kuyruğa alındı.');

            return self::SUCCESS;
        }

        $result = $service->scan();

        $this->info("{$result['checked']} adres denetlendi, {$result['broken']} kırık link var ({$result['removed']} eski kayıt temizlendi).");

        return self::SUCCESS;
    }
}
