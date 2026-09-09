<?php

namespace App\Support;

class Phone
{
    /**
     * Serbest formatlı bir telefon numarasını `tel:` bağlantısına çevirir.
     * Başında 0 varsa Türkiye kodu (90) ile değiştirilir: "0212 000 00 00" -> "tel:+902120000".
     */
    public static function href(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        return 'tel:+'.(str_starts_with($digits, '0') ? '90'.substr($digits, 1) : $digits);
    }
}
