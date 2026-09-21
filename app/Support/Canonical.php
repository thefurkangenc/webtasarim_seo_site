<?php

namespace App\Support;

/**
 * Sayfanın kendine dönen (self-referencing) canonical adresi.
 *
 * url()->current() sorgu dizesini tamamen atar; bu yüzden /blog?page=2
 * kendini /blog'un kopyası ilan ediyordu ve ikinci sayfadaki yazılar
 * indekslenmiyordu. url()->full() ise ters uca savurur: utm_source, gclid,
 * fbclid gibi her takip parametresi ayrı bir canonical üretir.
 *
 * Doğrusu ikisinin arası: yalnızca İÇERİĞİ DEĞİŞTİREN parametreler kalır,
 * geri kalan her şey atılır.
 */
final class Canonical
{
    /**
     * İçeriği değiştiren sorgu parametreleri. Ön yüzde şu an yalnızca
     * sayfalama var (blog/proje listeleri); filtreler adres segmentinde
     * yaşıyor (/blog/kategori/{slug}), sorgu dizesinde değil.
     */
    private const CONTENT_PARAMS = ['page'];

    public static function current(): string
    {
        $url = url()->current();
        $keep = [];

        foreach (self::CONTENT_PARAMS as $key) {
            $value = request()->query($key);

            // page=1 ilk sayfanın kendisidir, ayrı bir adres değil —
            // /blog ile /blog?page=1 tek canonical'da buluşmalı.
            if (! is_scalar($value) || (string) $value === '' || ($key === 'page' && (string) $value === '1')) {
                continue;
            }

            $keep[$key] = (string) $value;
        }

        return $keep === [] ? $url : $url.'?'.http_build_query($keep);
    }
}
