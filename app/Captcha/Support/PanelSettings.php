<?php

namespace App\Captcha\Support;

use App\Support\Settings;
use Throwable;

/**
 * Panel köprüsü. Ayarlar > Güvenlik Doğrulaması sekmesindeki değerleri
 * config/captcha yapısına çevirir.
 *
 * Köprü bilerek bu klasörün İÇİNDE duruyor: App\Support\Settings olmayan bir
 * projeye klasör kopyalandığında class_exists kontrolü devreye girer, hiçbir
 * şey patlamaz ve app/Captcha/config.php'deki değerler geçerli olur.
 */
final class PanelSettings
{
    /** @return array<string, mixed> */
    public static function overrides(): array
    {
        if (! class_exists(Settings::class)) {
            return [];
        }

        try {
            $values = Settings::group('captcha');
        } catch (Throwable) {
            // Kurulum öncesi settings tablosu henüz yokken sessizce config'e düş.
            return [];
        }

        if ($values === []) {
            return [];
        }

        $overrides = [];

        if (filled($values['enabled'] ?? null)) {
            $overrides['enabled'] = filter_var($values['enabled'], FILTER_VALIDATE_BOOLEAN);
        }

        if (filled($values['driver'] ?? null)) {
            $overrides['driver'] = (string) $values['driver'];
        }

        foreach (array_keys((array) config('captcha.forms', [])) as $form) {
            if (array_key_exists('form_'.$form, $values)) {
                $overrides['forms'][$form] = filter_var($values['form_'.$form], FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (filled($values['tolerance'] ?? null)) {
            $overrides['puzzle']['tolerance'] = (int) $values['tolerance'];
        }

        return $overrides;
    }
}
