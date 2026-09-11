<?php

namespace App\Console\Commands;

use App\Services\Sitemap\SitemapService;
use Illuminate\Console\Command;

class SitemapGenerateCommand extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Site haritası (sitemap.xml) dosyalarını yeniden oluşturur.';

    public function handle(SitemapService $sitemap): int
    {
        $report = $sitemap->generate();
        $this->info("Site haritası oluşturuldu: {$report['total']} adres.");

        return self::SUCCESS;
    }
}
