<?php

namespace App\Console\Commands;

use App\Services\Report\WeeklyReportService;
use Illuminate\Console\Command;

class WeeklyReportCommand extends Command
{
    protected $signature = 'report:weekly';

    protected $description = 'Site sahibine son 7 günün trafik ve mesaj özetini e-posta ile gönderir';

    public function handle(WeeklyReportService $reports): int
    {
        if (! $reports->send()) {
            $this->warn('Alıcı e-posta adresi yok (Ayarlar → İletişim / Şirket).');

            return self::FAILURE;
        }

        $this->info('Haftalık özet gönderildi.');

        return self::SUCCESS;
    }
}
