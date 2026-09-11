<?php

namespace App\Console\Commands;

use App\Services\Health\HealthService;
use Illuminate\Console\Command;

class HealthCheckCommand extends Command
{
    protected $signature = 'health:check
                            {--notify : Kritik sorun varsa yöneticiye e-posta gönder}';

    protected $description = 'Sistem sağlığı kontrollerini çalıştırır ve sonucu önbelleğe yazar';

    public function handle(HealthService $health): int
    {
        $report = $health->report(fresh: true);

        foreach ($report['checks'] as $check) {
            $this->line(sprintf(
                '  <fg=%s>%-9s</> %-24s %s',
                match ($check['status']) {
                    'critical' => 'red',
                    'warning' => 'yellow',
                    'skipped' => 'gray',
                    default => 'green',
                },
                config('health.statuses.'.$check['status'].'.label'),
                $check['label'],
                $check['message'],
            ));
        }

        $this->newLine();

        if ($this->option('notify') && $health->notify()) {
            $this->info('Kritik sorunlar için uyarı e-postası gönderildi.');
        }

        if ($report['counts']['critical'] > 0) {
            $this->error($report['counts']['critical'].' kritik sorun var.');

            return self::FAILURE;
        }

        $this->info($report['counts']['warning'] > 0
            ? $report['counts']['warning'].' uyarı var, kritik sorun yok.'
            : 'Her şey yolunda.');

        return self::SUCCESS;
    }
}
