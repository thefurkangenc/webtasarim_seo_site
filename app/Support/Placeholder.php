<?php

namespace App\Support;

/**
 * İçerik yer tutucularını çözer.
 *
 * Hizmet içerikleri tek kez yazılır, her hizmet bölgesi için yeniden
 * üretilir — metindeki {{region}} / {{city}} / {{district}} anahtarları o
 * bölgenin değerleriyle değiştirilir:
 *
 *   Placeholder::replace('{{city}} Web Tasarım', $region->placeholders());
 *   // "Gaziantep Web Tasarım"
 *
 * Anahtarın iki yanındaki boşluk göz ardı edilir ({{ city }} da çalışır).
 * Haritada bulunmayan anahtar olduğu gibi bırakılır — yazım hatası sessizce
 * metni silmesin, gözle görülür kalsın.
 */
class Placeholder
{
    /** @param  array<string, string>  $values */
    public static function replace(?string $text, array $values): ?string
    {
        if (blank($text)) {
            return $text;
        }

        return preg_replace_callback(
            '/\{\{\s*([a-z_]+)\s*\}\}/i',
            fn (array $match) => $values[strtolower($match[1])] ?? $match[0],
            $text,
        );
    }

    /**
     * Yer tutucuları tamamen kaldırır. Bölgeden bağımsız olması gereken
     * türetmelerde kullanılır — örn. slug: "{{city}} Web Tasarım" başlığından
     * "city-web-tasarim" değil "web-tasarim" üretilmeli.
     */
    public static function strip(?string $text): ?string
    {
        if (blank($text)) {
            return $text;
        }

        return trim(preg_replace('/\{\{\s*[a-z_]+\s*\}\}/i', '', $text));
    }

    /**
     * Bir dizinin tüm metin değerlerinde yer tutucuları çözer (SEO meta dizisi
     * gibi). Dizi olmayan değerlere dokunmaz.
     *
     * @param  array<string, mixed>  $fields
     * @param  array<string, string>  $values
     * @return array<string, mixed>
     */
    public static function replaceAll(array $fields, array $values): array
    {
        return collect($fields)
            ->map(fn ($field) => is_string($field) ? self::replace($field, $values) : $field)
            ->all();
    }

    /**
     * strip()'i bir dizinin tüm metin değerlerine uygular — replaceAll()'ın
     * yer-tutucusuz karşılığı. Bölge seçilmeden görüntülenen genel/şemsiye
     * sayfalar için (SEO meta dizisi gibi).
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    public static function stripAll(array $fields): array
    {
        return collect($fields)
            ->map(fn ($field) => is_string($field) ? self::strip($field) : $field)
            ->all();
    }
}
