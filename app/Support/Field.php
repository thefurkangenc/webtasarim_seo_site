<?php

namespace App\Support;

/**
 * Form alanı adı dönüşümleri.
 *
 * Bileşenlere alan adı her zaman nokta notasyonu ile verilir ('seo.meta_title').
 * Laravel doğrulama hatalarını bu anahtarla döndürdüğü için `data-error`
 * yuvaları da nokta notasyonunda kalır; yalnızca HTML `name` ve `id`
 * öznitelikleri dönüştürülür.
 *
 *   seo.meta_title  ->  name="seo[meta_title]"  id="seo-meta_title"
 *   title           ->  name="title"            id="title"
 */
class Field
{
    public static function name(string $key): string
    {
        $parts = explode('.', $key);
        $first = array_shift($parts);

        return $parts === []
            ? $first
            : $first.collect($parts)->map(fn (string $part) => "[{$part}]")->implode('');
    }

    public static function id(string $key): string
    {
        return str_replace('.', '-', $key);
    }
}
