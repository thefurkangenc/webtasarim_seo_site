<?php

namespace App\Support;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Dinamik sayfaların çalamayacağı kök URL segmentleri.
 *
 * Sayfa route'u `web.php`'nin en sonundaki çok segmentli bir catch-all'dır;
 * Laravel route'ları kayıt sırasına göre eşleştirdiği için mevcut sayfalar
 * (/blog, /hizmetler, /iletisim...) her zaman önce kazanır. Yani bir sayfaya
 * `blog` slug'ı verilse bile kırılma olmaz — sayfa yalnızca **erişilemez**
 * olur, ki bu sessiz ve kafa karıştırıcı bir sonuçtur.
 *
 * Bu sınıf o sessiz sonucu doğrulama hatasına çevirir: kayıtlı her route'un
 * sabit ilk segmenti toplanır, `config('pages.reserved')` ile birleştirilir.
 * Liste route tablosundan türetildiği için yeni bir modül eklendiğinde
 * kendiliğinden güncellenir, elle bakım gerektirmez.
 *
 *   ReservedPath::taken('blog')   // true
 *   ReservedPath::taken('kariyer') // false
 */
class ReservedPath
{
    /**
     * Kayıtlı route'ların sabit ilk segmentleri + config'teki ekler.
     *
     * @return array<int, string>
     */
    public static function segments(): array
    {
        $fromRoutes = collect(Route::getRoutes()->getRoutes())
            ->map(fn (RoutingRoute $route) => Str::before($route->uri(), '/'))
            // Kök route ('/') boş string verir; `{path}` gibi dinamik bir ilk
            // segment ise bir slug'ı engellemez — ikisi de atılır.
            ->reject(fn (string $segment) => $segment === '' || str_contains($segment, '{'));

        return $fromRoutes
            ->merge(config('pages.reserved', []))
            ->map(fn (string $segment) => mb_strtolower($segment))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public static function taken(?string $segment): bool
    {
        return filled($segment) && in_array(mb_strtolower($segment), self::segments(), true);
    }
}
