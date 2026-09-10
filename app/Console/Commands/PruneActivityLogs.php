<?php

namespace App\Console\Commands;

use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Console\Command;

/**
 * Saklama süresini aşan denetim kayıtlarını siler.
 *
 * BİLEREK zamanlanmamıştır: denetim kaydının ne zaman silineceği bir
 * güvenlik/uyum kararıdır, sessizce çalışan bir görev olmamalı. Otomatik
 * çalışmasını istersen routes/console.php içine bir schedule satırı ekle.
 *
 *   php artisan activity-log:prune            (config'teki süreyi kullanır)
 *   php artisan activity-log:prune --days=90
 *   php artisan activity-log:prune --days=90 --force
 */
class PruneActivityLogs extends Command
{
    protected $signature = 'activity-log:prune
                            {--days= : Kaç günden eski kayıtlar silinsin (varsayılan: config)}
                            {--force : Onay sorma}';

    protected $description = 'Saklama süresini aşan denetim kayıtlarını siler.';

    public function handle(ActivityLogService $service): int
    {
        $days = (int) ($this->option('days') ?: config('activity-log.retention_days'));

        if ($days < 1) {
            $this->error('Gün sayısı en az 1 olmalı.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);

        if (! $this->option('force')
            && ! $this->confirm("{$cutoff->format('d.m.Y')} tarihinden eski tüm log kayıtları KALICI olarak silinecek. Devam edilsin mi?")) {
            $this->comment('İptal edildi.');

            return self::SUCCESS;
        }

        $deleted = $service->prune($days);

        $this->info("{$deleted} log kaydı silindi ({$days} günden eski).");

        return self::SUCCESS;
    }
}
