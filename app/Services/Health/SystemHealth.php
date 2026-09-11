<?php

namespace App\Services\Health;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Sunucu tarafı kontroller: disk, yazma izinleri, veritabanı, zamanlanmış
 * görevlerin cron'u ve hata ayıklama modu.
 */
class SystemHealth
{
    /** `schedule:run` her dakika bu anahtarı tazeler (bootstrap/app.php). */
    public const CRON_KEY = 'health.cron.heartbeat';

    /** @return array<int, Check> */
    public function checks(): array
    {
        return [$this->cron(), $this->disk(), $this->database(), $this->writable(), $this->debug()];
    }

    private function cron(): Check
    {
        $value = Cache::get(self::CRON_KEY);
        $last = $value ? Carbon::parse($value) : null;
        $meta = [
            'last_run' => $last?->toIso8601String(),
            'last_run_label' => $last?->diffForHumans(),
        ];
        $hint = 'Sunucunun crontab\'ına dakikada bir çalışan `php artisan schedule:run` satırı eklenmeli.';

        if ($last === null) {
            return Check::critical('cron', 'Zamanlanmış Görevler',
                'Cron hiç çalışmamış. Site haritası, kırık link taraması ve sağlık kontrolü otomatik yenilenmiyor.',
                $hint, $meta);
        }

        if ($last->diffInMinutes(now()) > (int) config('health.cron_minutes', 30)) {
            return Check::critical('cron', 'Zamanlanmış Görevler',
                'Cron en son '.$last->diffForHumans().' çalışmış.', $hint, $meta);
        }

        return Check::ok('cron', 'Zamanlanmış Görevler',
            'Son çalışma '.$last->diffForHumans().'.', $meta);
    }

    private function disk(): Check
    {
        $free = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());

        if ($free === false || $total === false || $total <= 0) {
            return Check::skipped('disk', 'Disk Alanı', 'Disk bilgisi okunamadı (barındırma kısıtlaması olabilir).');
        }

        $used = round((($total - $free) / $total) * 100, 1);
        $meta = [
            'free' => $this->bytes($free),
            'total' => $this->bytes($total),
            'used_percent' => $used,
        ];
        $message = 'Doluluk %'.$used.' · '.$this->bytes($free).' boş / '.$this->bytes($total).' toplam';
        $hint = 'Eski medya dosyalarını, günlükleri ve yedekleri temizleyin.';

        if ($used >= (float) config('health.disk.critical_percent', 95)) {
            return Check::critical('disk', 'Disk Alanı', $message, $hint, $meta);
        }

        if ($used >= (float) config('health.disk.warning_percent', 85)) {
            return Check::warning('disk', 'Disk Alanı', $message, $hint, $meta);
        }

        return Check::ok('disk', 'Disk Alanı', $message, $meta);
    }

    private function database(): Check
    {
        $started = microtime(true);

        try {
            DB::connection()->getPdo();
            DB::select('select 1');
        } catch (Throwable $e) {
            return Check::critical('database', 'Veritabanı', 'Bağlantı kurulamadı: '.$e->getMessage(),
                'Veritabanı sunucusunu ve .env bağlantı bilgilerini kontrol edin.');
        }

        $ms = (int) round((microtime(true) - $started) * 1000);

        return Check::ok('database', 'Veritabanı',
            strtoupper((string) DB::connection()->getDriverName()).' bağlantısı sağlıklı · '.$ms.' ms',
            ['response_ms' => $ms, 'driver' => DB::connection()->getDriverName()]);
    }

    private function writable(): Check
    {
        $failed = [];

        foreach (config('health.writable_paths', []) as $path => $label) {
            if (! is_writable(base_path($path))) {
                $failed[] = $path.' ('.$label.')';
            }
        }

        if ($failed !== []) {
            return Check::critical('writable', 'Yazma İzinleri',
                'Yazılamayan dizin: '.implode(', ', $failed),
                'Dizin sahipliğini web sunucusu kullanıcısına verin (ör. chown -R www-data).',
                ['failed' => $failed]);
        }

        return Check::ok('writable', 'Yazma İzinleri',
            count(config('health.writable_paths', [])).' dizinin tamamı yazılabilir.');
    }

    private function debug(): Check
    {
        $debug = (bool) config('app.debug');
        $env = (string) config('app.env');
        $meta = ['debug' => $debug, 'env' => $env];

        if ($debug && $env === 'production') {
            return Check::critical('debug', 'Hata Ayıklama Modu',
                'APP_DEBUG canlı ortamda açık. Hata ekranları veritabanı bilgilerini ve dosya yollarını gösterir.',
                '.env dosyasında APP_DEBUG=false yapıp `php artisan config:clear` çalıştırın.', $meta);
        }

        if ($env !== 'production') {
            return Check::warning('debug', 'Çalışma Ortamı',
                'Uygulama "'.$env.'" ortamında çalışıyor.',
                'Canlı sunucuda APP_ENV=production olmalı.', $meta);
        }

        return Check::ok('debug', 'Hata Ayıklama Modu', 'Canlı ortam, hata ayıklama kapalı.', $meta);
    }

    private function bytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i > 2 ? 1 : 0).' '.$units[$i];
    }
}
