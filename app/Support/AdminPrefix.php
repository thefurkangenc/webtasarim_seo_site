<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Panel URL'nin ilk segmenti. Route adları admin.* kalır.
 *
 * Kaynak: config/admin.php → env('ADMIN_PREFIX', 'admin'). Veritabanı yok.
 */
class AdminPrefix
{
    public static function get(): string
    {
        $value = self::normalize((string) config('admin.prefix', 'admin'));

        return $value !== '' ? $value : 'admin';
    }

    private static function normalize(?string $value): string
    {
        return trim(strtolower(trim((string) $value)), '/');
    }

    public static function is(Request $request): bool
    {
        $prefix = self::get();

        return $request->is($prefix, $prefix.'/*');
    }
}
