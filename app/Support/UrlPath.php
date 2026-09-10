<?php

namespace App\Support;

/**
 * Yönlendirme eşleşmesi için yol normalleştirmesi. Kaynak yollar da,
 * gelen istek yolu da aynı kurala sokulur ki karşılaştırma tutarlı olsun.
 */
class UrlPath
{
    /**
     * "/Eski-URL/?utm=1" → "eski-url"
     *
     * - baştaki/sondaki eğik çizgiler atılır
     * - sorgu dizesi ve fragment atılır
     * - küçük harfe indirilir (adresler büyük/küçük harf duyarsız eşleşir)
     * - yüzde kodlaması çözülür ("%20" → boşluk)
     */
    public static function normalize(?string $path): string
    {
        $path = (string) $path;

        // Tam URL verildiyse yalnızca yol kısmını al.
        if (str_contains($path, '://')) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = explode('?', $path, 2)[0];
        $path = explode('#', $path, 2)[0];
        $path = rawurldecode($path);

        return mb_strtolower(trim($path, '/'));
    }

    /** Hedef bir dış URL mi (yönlendirme `away` ile mi yapılmalı). */
    public static function isExternal(string $url): bool
    {
        return str_contains($url, '://');
    }
}
