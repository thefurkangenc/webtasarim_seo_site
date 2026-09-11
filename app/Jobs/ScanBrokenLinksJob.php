<?php

namespace App\Jobs;

use App\Services\BrokenLink\BrokenLinkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Kırık link taraması. Dış adresler tek tek denendiği için uzun sürebilir,
 * bu yüzden panelden istek beklemeden kuyruğa atılır. `ShouldBeUnique`
 * butona üst üste basılsa da tek tarama çalışmasını sağlar.
 */
class ScanBrokenLinksJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 1;

    public int $uniqueFor = 1800;

    public function handle(BrokenLinkService $service): void
    {
        $service->scan();
    }
}
