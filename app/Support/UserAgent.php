<?php

namespace App\Support;

/**
 * User-Agent başlığını tarayıcı / işletim sistemi / cihaz bilgisine ayrıştırır.
 *
 * Harici paket kullanılmıyor: log kayıtlarında ihtiyacımız olan şey ikon
 * eşlemesi ve okunabilir bir etiket; bunun için yüzlerce cihaz modelini bilen
 * ağır bir kütüphaneye gerek yok. Sıra ÖNEMLİDİR — birçok tarayıcı kendini
 * Chrome/Safari gibi tanıtır, bu yüzden özel olanlar önce sınanır.
 *
 *   $agent = UserAgent::parse($request->userAgent());
 *   $agent['browser'];      // "Google Chrome"
 *   $agent['device_type'];  // "desktop"
 */
class UserAgent
{
    /**
     * [anahtar, etiket, eşleşme deseni, sürüm deseni]
     *
     * Sıralama kritik: Edge kendini "Chrome" ve "Safari" olarak da tanıtır,
     * Chrome da "Safari" olarak. Bu yüzden en spesifik olan en üstte.
     *
     * @var array<int, array{0: string, 1: string, 2: string, 3: string|null}>
     */
    private const BROWSERS = [
        ['edge', 'Microsoft Edge', '/Edg(?:e|A|iOS)?\//i', '/Edg(?:e|A|iOS)?\/([\d.]+)/i'],
        ['opera', 'Opera', '/OPR\/|Opera/i', '/(?:OPR|Opera)[\/ ]([\d.]+)/i'],
        ['samsung', 'Samsung Internet', '/SamsungBrowser/i', '/SamsungBrowser\/([\d.]+)/i'],
        ['yandex', 'Yandex Browser', '/YaBrowser/i', '/YaBrowser\/([\d.]+)/i'],
        ['brave', 'Brave', '/Brave/i', '/Brave\/([\d.]+)/i'],
        ['vivaldi', 'Vivaldi', '/Vivaldi/i', '/Vivaldi\/([\d.]+)/i'],
        ['firefox', 'Mozilla Firefox', '/Firefox\/|FxiOS/i', '/(?:Firefox|FxiOS)\/([\d.]+)/i'],
        ['chrome', 'Google Chrome', '/Chrome\/|CriOS/i', '/(?:Chrome|CriOS)\/([\d.]+)/i'],
        ['safari', 'Safari', '/Safari\//i', '/Version\/([\d.]+)/i'],
        ['ie', 'Internet Explorer', '/MSIE |Trident\//i', '/(?:MSIE |rv:)([\d.]+)/i'],
    ];

    /**
     * @var array<int, array{0: string, 1: string, 2: string, 3: string|null}>
     */
    private const PLATFORMS = [
        // Windows NT sürümü pazarlama adına çevrilir (aşağıda windowsName()).
        ['windows', 'Windows', '/Windows NT/i', '/Windows NT ([\d.]+)/i'],
        ['android', 'Android', '/Android/i', '/Android ([\d.]+)/i'],
        ['ios', 'iOS', '/iPhone|iPad|iPod/i', '/OS ([\d_]+)/i'],
        ['macos', 'macOS', '/Mac OS X|Macintosh/i', '/Mac OS X ([\d_.]+)/i'],
        ['ubuntu', 'Ubuntu', '/Ubuntu/i', null],
        ['linux', 'Linux', '/Linux|X11/i', null],
        ['chromeos', 'ChromeOS', '/CrOS/i', null],
    ];

    /** Bot/tarayıcı olmayan istemciler — ayrı işaretlenir, cihaz tipi "bot" olur. */
    private const BOT_PATTERN = '/bot|crawler|spider|crawling|slurp|facebookexternalhit|'
        .'whatsapp|telegram|preview|curl|wget|python-requests|axios|postman|headless|'
        .'lighthouse|pingdom|uptime|monitor|scrapy|semrush|ahrefs|mj12|dotbot/i';

    /** Windows NT sürüm numarası -> pazarlama adı. */
    private const WINDOWS_VERSIONS = [
        '10.0' => '10/11',
        '6.3' => '8.1',
        '6.2' => '8',
        '6.1' => '7',
        '6.0' => 'Vista',
        '5.1' => 'XP',
    ];

    /**
     * @return array{
     *     browser: string|null, browser_key: string|null, browser_version: string|null,
     *     platform: string|null, platform_key: string|null, platform_version: string|null,
     *     device_type: string, device_brand: string|null, is_bot: bool
     * }
     */
    public static function parse(?string $agent): array
    {
        $agent = trim((string) $agent);

        $result = [
            'browser' => null,
            'browser_key' => null,
            'browser_version' => null,
            'platform' => null,
            'platform_key' => null,
            'platform_version' => null,
            'device_type' => 'unknown',
            'device_brand' => null,
            'is_bot' => false,
        ];

        if ($agent === '') {
            return $result;
        }

        if (preg_match(self::BOT_PATTERN, $agent)) {
            $result['is_bot'] = true;
            $result['device_type'] = 'bot';
            $result['browser'] = self::botName($agent);
            $result['browser_key'] = 'bot';

            return $result;
        }

        foreach (self::BROWSERS as [$key, $label, $match, $version]) {
            if (! preg_match($match, $agent)) {
                continue;
            }

            $result['browser'] = $label;
            $result['browser_key'] = $key;
            $result['browser_version'] = self::version($agent, $version);
            break;
        }

        foreach (self::PLATFORMS as [$key, $label, $match, $version]) {
            if (! preg_match($match, $agent)) {
                continue;
            }

            $result['platform'] = $label;
            $result['platform_key'] = $key;
            $result['platform_version'] = self::platformVersion($key, $agent, $version);
            break;
        }

        $result['device_type'] = self::deviceType($agent, $result['platform_key']);
        $result['device_brand'] = self::deviceBrand($agent, $result['platform_key']);

        return $result;
    }

    private static function version(string $agent, ?string $pattern): ?string
    {
        if (! $pattern || ! preg_match($pattern, $agent, $matches)) {
            return null;
        }

        // Uzun sürümler (120.0.6099.109) listede yer kaplıyor; ana sürüm yeter.
        return explode('.', str_replace('_', '.', $matches[1]))[0] ?: null;
    }

    private static function platformVersion(string $key, string $agent, ?string $pattern): ?string
    {
        if (! $pattern || ! preg_match($pattern, $agent, $matches)) {
            return null;
        }

        $raw = str_replace('_', '.', $matches[1]);

        if ($key === 'windows') {
            return self::WINDOWS_VERSIONS[$raw] ?? $raw;
        }

        // macOS/iOS'ta ana ve alt sürüm birlikte anlamlı (14.4 gibi).
        $parts = explode('.', $raw);

        return implode('.', array_slice($parts, 0, 2));
    }

    private static function deviceType(string $agent, ?string $platform): string
    {
        if (preg_match('/iPad|Tablet|PlayBook|Silk|Kindle/i', $agent)) {
            return 'tablet';
        }

        // Android'de "Mobile" ibaresi yoksa cihaz tablettir (Android'in kendi kuralı).
        if ($platform === 'android') {
            return preg_match('/Mobile/i', $agent) ? 'mobile' : 'tablet';
        }

        if (preg_match('/Mobi|iPhone|iPod|Windows Phone|IEMobile|Opera Mini/i', $agent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private static function deviceBrand(string $agent, ?string $platform): ?string
    {
        if ($platform === 'ios') {
            return 'Apple';
        }

        if ($platform === 'macos') {
            return 'Apple';
        }

        $brands = [
            'Samsung' => '/SM-|Samsung|GT-/i',
            'Huawei' => '/HUAWEI|Honor|\bHW-/i',
            'Xiaomi' => '/Xiaomi|Redmi|POCO|MI \d/i',
            'Oppo' => '/OPPO|CPH\d/i',
            'Vivo' => '/\bvivo\b/i',
            'OnePlus' => '/OnePlus/i',
            'Google' => '/Pixel/i',
            'Nokia' => '/Nokia/i',
            'LG' => '/\bLG-|LM-[A-Z]\d/i',
        ];

        foreach ($brands as $brand => $pattern) {
            if (preg_match($pattern, $agent)) {
                return $brand;
            }
        }

        return null;
    }

    /** Bilinen botlar okunabilir adla, diğerleri genel etiketle gösterilir. */
    private static function botName(string $agent): string
    {
        $known = [
            'Googlebot' => '/Googlebot/i',
            'Bingbot' => '/bingbot|BingPreview/i',
            'YandexBot' => '/YandexBot/i',
            'DuckDuckBot' => '/DuckDuckBot/i',
            'Baiduspider' => '/Baiduspider/i',
            'AhrefsBot' => '/AhrefsBot/i',
            'SemrushBot' => '/SemrushBot/i',
            'Facebook' => '/facebookexternalhit/i',
            'WhatsApp' => '/WhatsApp/i',
            'Telegram' => '/TelegramBot/i',
            'Twitter' => '/Twitterbot/i',
            'cURL' => '/curl/i',
            'Wget' => '/Wget/i',
            'Postman' => '/Postman/i',
            'Python' => '/python-requests|Scrapy/i',
        ];

        foreach ($known as $name => $pattern) {
            if (preg_match($pattern, $agent)) {
                return $name;
            }
        }

        return 'Bot / Otomasyon';
    }
}
