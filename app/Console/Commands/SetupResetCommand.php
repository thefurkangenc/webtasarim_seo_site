<?php

namespace App\Console\Commands;

use App\Services\Setup\SetupService;
use Illuminate\Console\Command;

class SetupResetCommand extends Command
{
    protected $signature = 'setup:reset
                            {--force : Onay sormadan çalıştır}';

    protected $description = 'İçeriği, kullanıcıları ve ayarları siler; kurulum sihirbazını yeniden açar.';

    public function handle(SetupService $setup): int
    {
        $database = (string) config('database.connections.'.config('database.default').'.database');

        if (! $this->option('force') && ! $this->confirm("{$database} içindeki içerik, kullanıcılar ve ayarlar silinecek. Kurulum sihirbazı yeniden açılır. Devam?")) {
            $this->warn('İptal edildi.');

            return self::SUCCESS;
        }

        $setup->reset();

        $this->info('Sıfırlandı. Siteyi açınca /kurulum sihirbazı gelir.');

        return self::SUCCESS;
    }
}
