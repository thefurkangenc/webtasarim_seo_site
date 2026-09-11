<?php

namespace App\Services\Health;

use App\Services\Analytics\AnalyticsService;
use App\Services\Google\GoogleException;
use App\Services\Setting\SettingService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * Dışarıya bağlanan kontroller: sertifika, SMTP ve Google servisleri.
 * Hepsi ağ gerektirir — bu yüzden rapor cache'lenir, her sayfa açılışında
 * yeniden çalıştırılmaz.
 */
class ConnectivityHealth
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly AnalyticsService $analytics,
    ) {}

    /** @return array<int, Check> */
    public function checks(): array
    {
        return [$this->ssl(), $this->smtp(), ...$this->google()];
    }

    private function ssl(): Check
    {
        $url = (string) config('app.url');
        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! $host || $scheme !== 'https') {
            return Check::skipped('ssl', 'SSL Sertifikası',
                'Site adresi https değil ('.$url.'), sertifika kontrol edilmedi.',
                'Canlıya çıkarken .env dosyasındaki APP_URL https olmalı.');
        }

        $expiresAt = $this->certificateExpiry($host);

        if ($expiresAt instanceof Check) {
            return $expiresAt;
        }

        $days = (int) floor(now()->diffInDays($expiresAt, false));
        $meta = [
            'host' => $host,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_label' => $expiresAt->translatedFormat('d F Y'),
            'days_left' => $days,
        ];
        $hint = 'Otomatik yenileme (certbot / barındırma paneli) çalışıyor mu kontrol edin.';

        if ($days < 0) {
            return Check::critical('ssl', 'SSL Sertifikası',
                'Sertifikanın süresi '.$expiresAt->translatedFormat('d F Y').' tarihinde doldu.', $hint, $meta);
        }

        $message = $days.' gün kaldı · '.$expiresAt->translatedFormat('d F Y').' tarihinde bitiyor';

        if ($days <= (int) config('health.ssl.critical_days', 7)) {
            return Check::critical('ssl', 'SSL Sertifikası', $message, $hint, $meta);
        }

        if ($days <= (int) config('health.ssl.warning_days', 21)) {
            return Check::warning('ssl', 'SSL Sertifikası', $message, $hint, $meta);
        }

        return Check::ok('ssl', 'SSL Sertifikası', $message, $meta);
    }

    /** Sertifikayı okuyamazsa hazır bir Check döner; okursa bitiş tarihini. */
    private function certificateExpiry(string $host): Carbon|Check
    {
        $timeout = (int) config('health.ssl.timeout', 5);
        $context = stream_context_create(['ssl' => [
            'capture_peer_cert' => true,
            'SNI_enabled' => true,
            // Zincir doğrulaması ayrı bir dert; burada sorulan tek şey bitiş tarihi.
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]]);

        $client = @stream_socket_client(
            'ssl://'.$host.':443', $errno, $error, $timeout, STREAM_CLIENT_CONNECT, $context
        );

        if ($client === false) {
            return Check::warning('ssl', 'SSL Sertifikası',
                $host.' adresine bağlanılamadı: '.($error ?: 'bilinmeyen hata'),
                'Sunucu dışarıya bağlanamıyor olabilir; tarayıcıda sertifika geçerliyse bu uyarı yok sayılabilir.');
        }

        $params = stream_context_get_params($client);
        fclose($client);

        $certificate = $params['options']['ssl']['peer_certificate'] ?? null;
        $parsed = $certificate ? openssl_x509_parse($certificate) : false;

        if (! is_array($parsed) || ! isset($parsed['validTo_time_t'])) {
            return Check::warning('ssl', 'SSL Sertifikası', 'Sertifika okundu ama bitiş tarihi çözümlenemedi.');
        }

        return Carbon::createFromTimestamp((int) $parsed['validTo_time_t']);
    }

    private function smtp(): Check
    {
        $mail = $this->settings->getGroup('mail');

        if (blank($mail['host'] ?? null) || blank($mail['from_address'] ?? null)) {
            return Check::skipped('smtp', 'E-posta (SMTP)',
                'SMTP bilgileri girilmemiş; form bildirimleri ve panelden yanıtlar gönderilemez.',
                'Ayarlar → Posta ekranından sunucu bilgilerini girin.');
        }

        try {
            // Kayıtlı şifre kullanılsın diye boş geçiliyor; testMail yalnızca
            // bağlantı kurup kapatır, e-posta göndermez.
            $this->settings->testMail([
                'host' => $mail['host'],
                'port' => $mail['port'] ?? 587,
                'username' => $mail['username'] ?? '',
                'password' => null,
                'encryption' => $mail['encryption'] ?? 'tls',
            ]);
        } catch (DomainException $e) {
            return Check::critical('smtp', 'E-posta (SMTP)', $e->getMessage(),
                'Ayarlar → Posta ekranındaki bilgileri kontrol edin.',
                ['host' => $mail['host']]);
        } catch (Throwable $e) {
            return Check::critical('smtp', 'E-posta (SMTP)', 'Bağlantı kurulamadı: '.$e->getMessage(),
                'Ayarlar → Posta ekranındaki bilgileri kontrol edin.');
        }

        return Check::ok('smtp', 'E-posta (SMTP)',
            $mail['host'].' bağlantısı ve kimlik doğrulaması başarılı.',
            ['host' => $mail['host'], 'from' => $mail['from_address']]);
    }

    /**
     * GA4 ve Search Console aynı service account'u paylaşır; jeton kapsam
     * başına alınır, biri çalışıp diğeri çalışmayabilir (API açık değilse).
     *
     * @return array<int, Check>
     */
    private function google(): array
    {
        $account = $this->analytics->serviceAccount();

        if ($account === null) {
            return [Check::skipped('google', 'Google Servisleri',
                'Service account yüklenmemiş; GA4 ve Search Console verisi çekilemez.',
                'Ayarlar → Analitik ekranından JSON anahtarını yükleyin.')];
        }

        $checks = [];

        foreach (config('health.google_scopes', []) as $label => $scope) {
            $key = 'google_'.Str::slug($label, '_');

            try {
                $account->accessToken($scope);
                $checks[] = Check::ok($key, $label, 'Erişim jetonu alındı, kimlik geçerli.');
            } catch (GoogleException $e) {
                $checks[] = Check::warning($key, $label, $e->getMessage(),
                    'Cloud projesinde ilgili API etkin mi ve service account e-postası mülke eklenmiş mi kontrol edin.');
            } catch (Throwable $e) {
                $checks[] = Check::warning($key, $label, 'Jeton alınamadı: '.$e->getMessage());
            }
        }

        return $checks;
    }
}
