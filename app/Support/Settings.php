<?php

namespace App\Support;

use App\Services\Setting\SettingService;

/**
 * Site ayarlarını nokta notasyonuyla okur: Settings::get('company.phone').
 * Yazma SettingService::putGroup() üzerinden yapılır.
 */
class Settings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        [$group, $name] = array_pad(explode('.', $key, 2), 2, null);

        if ($group === null || $name === null) {
            return $default;
        }

        return app(SettingService::class)->get($group, $name, $default);
    }

    /**
     * Ayar değeri PHP'de (bool)"0" true olduğu için filter_var ile okunur.
     */
    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null || $value === '') {
            $defaults = config('settings.defaults');
            [$group, $name] = array_pad(explode('.', $key, 2), 2, null);
            $fallback = $defaults[$group][$name] ?? null;

            if ($fallback === null || $fallback === '') {
                return $default;
            }

            return filter_var($fallback, FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /** @return array<string, string|null> */
    public static function group(string $group): array
    {
        return app(SettingService::class)->getGroup($group);
    }

    /**
     * Kayıtlı değerleri config varsayılanlarıyla birleştirir.
     *
     * @return array<string, string|null>
     */
    public static function merged(string $group): array
    {
        return array_replace(config('settings.defaults.'.$group, []), self::group($group));
    }
}
