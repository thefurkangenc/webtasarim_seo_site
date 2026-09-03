<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Türkçe karakter duyarlı, tabloda benzersiz slug üretir.
 *
 *   Slug::unique('Şeker Fabrikası', 'blogs');          // seker-fabrikasi
 *   Slug::unique($title, 'blogs', ignoreId: $blog->id) // düzenlemede kendini saymaz
 */
class Slug
{
    public static function unique(string $value, string $table, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $base = Str::slug($value, '-', 'tr') ?: 'kayit';
        $slug = $base;
        $suffix = 1;

        while (self::exists($slug, $table, $column, $ignoreId)) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    private static function exists(string $slug, string $table, string $column, ?int $ignoreId): bool
    {
        return DB::table($table)
            ->where($column, $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
