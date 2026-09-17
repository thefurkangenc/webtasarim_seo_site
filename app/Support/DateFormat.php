<?php

namespace App\Support;

use Carbon\CarbonInterface;

/** Ön yüz tarihleri — site tek dil Türkçe, Carbon locale'e bağlı kalmaz. */
final class DateFormat
{
    /** @var array<int, string> */
    private const MONTHS_FULL = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
        5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
        9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
    ];

    /** @var array<int, string> */
    private const MONTHS_SHORT = [
        1 => 'Oca', 2 => 'Şub', 3 => 'Mar', 4 => 'Nis',
        5 => 'May', 6 => 'Haz', 7 => 'Tem', 8 => 'Ağu',
        9 => 'Eyl', 10 => 'Eki', 11 => 'Kas', 12 => 'Ara',
    ];

    public static function long(?CarbonInterface $date): ?string
    {
        if ($date === null) {
            return null;
        }

        return $date->day.' '.self::MONTHS_FULL[$date->month].' '.$date->year;
    }

    public static function short(?CarbonInterface $date): ?string
    {
        if ($date === null) {
            return null;
        }

        return $date->day.' '.self::MONTHS_SHORT[$date->month].' '.$date->year;
    }
}
