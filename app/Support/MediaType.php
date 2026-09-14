<?php

namespace App\Support;

/**
 * Uzantı -> tür (image / video / document) eşlemesinin tek yeri.
 * Kaynak `config/media.php` > `types`; burada yalnızca okunur.
 *
 * Üç yerde aynı listeye ihtiyaç var ve üçünün de birbirinden sapmaması
 * gerekiyor: Blade'deki HTML `accept` özniteliği, kütüphanedeki tür filtresi
 * ve sunucudaki yükleme kontrolü.
 */
class MediaType
{
    public const IMAGE = 'image';

    public const VIDEO = 'video';

    public const DOCUMENT = 'document';

    public const OTHER = 'other';

    /** Filtrelerde ve `accept` parametresinde geçerli değerler. */
    public static function keys(): array
    {
        return [self::IMAGE, self::VIDEO, self::DOCUMENT, self::OTHER];
    }

    /** Uzantının hangi gruba düştüğü; tanımsız uzantı 'other'. */
    public static function of(?string $extension): string
    {
        $extension = strtolower((string) $extension);

        foreach (config('media.types', []) as $type => $extensions) {
            if (in_array($extension, $extensions, true)) {
                return $type;
            }
        }

        return self::OTHER;
    }

    /**
     * Bir grubun uzantıları; tür verilmezse kabul edilen tüm uzantılar.
     *
     * @return list<string>
     */
    public static function extensions(?string $type = null): array
    {
        return $type
            ? array_values(config("media.types.{$type}", []))
            : array_values(config('media.accepts', []));
    }

    /**
     * `<input type="file" accept="...">` değeri — ".jpg,.png,..." biçiminde.
     * Tür verilmezse kütüphanenin kabul ettiği her şey.
     */
    public static function accept(?string $type = null): string
    {
        return collect(self::extensions($type))->map(fn (string $e) => ".{$e}")->implode(',');
    }

    /** Verilen uzantı bu gruba ait mi? Grup boşsa (kısıt yok) her zaman true. */
    public static function allows(?string $type, ?string $extension): bool
    {
        return blank($type) || self::of($extension) === $type;
    }
}
