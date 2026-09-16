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
 *
 * Bölgeye özel metin: iki {???} (ya da {{???}}) işareti arasındaki metin
 * yalnızca bölgeli sayfada görünür. replace() işaretleri atıp metni bırakır,
 * strip() işaretleri aradaki metinle birlikte siler:
 *
 *   "Özellikle {???}Gaziantep gibi {???}rekabetçi bölgelerde"
 *   replace -> "Özellikle Gaziantep gibi rekabetçi bölgelerde"
 *   strip   -> "Özellikle rekabetçi bölgelerde"
 *
 * Aralık birden çok HTML bloğunu kapsayabilir; geride kalan boş etiketler
 * (<h2></h2>, <p>&nbsp;</p>) temizlenir. Eşi olmayan tek işaret yalnızca
 * kendisi silinir — yazım hatası içeriğin geri kalanını yutmasın.
 */
class Placeholder
{
    private const KEY = '/\{\{\s*[a-z_]+\s*\}\}/i';

    private const REGION_MARK = '/\{\{?\?\?\?\}\}?/';

    /** İçi yalnızca boşluk / &nbsp; / <br> olan metin etiketleri. */
    private const EMPTY_TAG = '/<(p|h[1-6]|li|strong|b|em|i|u|span)(?:\s[^>]*)?>(?:\s|&nbsp;|&#160;|\x{00A0}|<br\s*\/?>)*<\/\1>/iu';

    /** @param  array<string, string>  $values */
    public static function replace(?string $text, array $values): ?string
    {
        if (blank($text)) {
            return $text;
        }

        $text = preg_replace_callback(
            '/\{\{\s*([a-z_]+)\s*\}\}/i',
            fn (array $match) => $values[strtolower($match[1])] ?? $match[0],
            $text,
        );

        return preg_match(self::REGION_MARK, $text)
            ? self::tidy(preg_replace(self::REGION_MARK, '', $text))
            : $text;
    }

    /**
     * Yer tutucuları ve bölgeye özel metni tamamen kaldırır. Bölgeden bağımsız
     * olması gereken türetmelerde kullanılır — örn. slug: "{{city}} Web Tasarım"
     * başlığından "city-web-tasarim" değil "web-tasarim" üretilmeli.
     */
    public static function strip(?string $text): ?string
    {
        if (blank($text)) {
            return $text;
        }

        $marked = preg_match(self::REGION_MARK, $text);

        if ($marked) {
            $mark = trim(self::REGION_MARK, '/');
            $text = preg_replace("/{$mark}.*?{$mark}/su", '', $text);
            $text = preg_replace(self::REGION_MARK, '', $text);
        }

        $text = trim(preg_replace(self::KEY, '', $text));

        return $marked ? self::tidy($text) : $text;
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

    /**
     * İşaret silindikten sonra kalan izleri toplar: boş etiketler (iç içe
     * olabilir, o yüzden değişiklik kalmayana dek) ve yan yana düşen boşluklar.
     * Yalnızca işaret bulunan metinde çalışır — editörün bilinçli bıraktığı
     * boş paragraflara dokunulmaz.
     */
    private static function tidy(string $text): string
    {
        do {
            $text = preg_replace(self::EMPTY_TAG, '', $text, -1, $count);
        } while ($count > 0);

        return trim(preg_replace('/[ \t]{2,}/', ' ', $text));
    }
}
