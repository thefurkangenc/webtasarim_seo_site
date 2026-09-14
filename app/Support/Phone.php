<?php

namespace App\Support;

use App\Models\Country\Country;

class Phone
{
    /** Serbest metinden yalnızca rakamları alır. */
    public static function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    /** Maskedeki `0` karakterleri hane yer tutucusudur. */
    public static function digitCount(string $mask): int
    {
        return substr_count($mask, '0');
    }

    /**
     * Ulusal numarayı ülkeye göre sadeleştirir: yalnızca rakam, TR'de baştaki
     * 0 düşer, hane sayısı maskenin üstüne çıkmaz.
     */
    public static function normalize(?string $value, ?Country $country): string
    {
        $digits = self::digits($value);

        if ($country?->stripsLeadingZero() && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        $max = $country?->digitCount() ?: 15;

        return substr($digits, 0, $max);
    }

    /** Rakamları maskeye yerleştirir (`0` = hane, diğer karakterler olduğu gibi). */
    public static function format(?string $digits, string $mask): string
    {
        $digits = self::digits($digits);
        $out = '';
        $index = 0;
        $length = strlen($digits);

        foreach (str_split($mask) as $char) {
            if ($char === '0') {
                if ($index >= $length) {
                    break;
                }

                $out .= $digits[$index++];

                continue;
            }

            if ($index >= $length) {
                break;
            }

            $out .= $char;
        }

        return $out;
    }

    public static function e164(?string $digits, ?Country $country): ?string
    {
        $digits = self::digits($digits);

        if ($digits === '' || ! $country) {
            return null;
        }

        return '+'.$country->dial_code.$digits;
    }

    /**
     * Serbest formatlı bir telefon numarasını `tel:` bağlantısına çevirir.
     * Ülke verilmişse E.164 üretir. Verilmemişse eski kural: başındaki 0
     * Türkiye kodu (90) ile değiştirilir.
     */
    public static function href(?string $phone, ?Country $country = null): ?string
    {
        if ($country) {
            $e164 = self::e164($phone, $country);

            return $e164 ? 'tel:'.$e164 : null;
        }

        $digits = self::digits($phone);

        if ($digits === '') {
            return null;
        }

        return 'tel:+'.(str_starts_with($digits, '0') ? '90'.substr($digits, 1) : $digits);
    }
}
