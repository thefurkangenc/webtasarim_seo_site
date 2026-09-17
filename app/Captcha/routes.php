<?php

use App\Captcha\Http\CaptchaController;
use App\Captcha\Support\Asset;
use Illuminate\Support\Facades\Route;

/*
| Captcha uç noktaları. bootstrap/app.php'deki `then:` kancasından, dinamik
| sayfaların catch-all'ından ÖNCE yüklenir.
|
| withoutMiddleware: bakım modu / kurulum sihirbazı ara katmanları bu üç
| adresi kesmemeli, yoksa site bakımdayken panel girişindeki doğrulama da
| çalışmaz. Projede olmayan sınıflar listeden düşer.
*/

Route::prefix((string) config('captcha.route_prefix', 'captcha'))
    ->name('captcha.')
    ->controller(CaptchaController::class)
    ->withoutMiddleware(array_values(array_filter((array) config('captcha.without_middleware', []), 'class_exists')))
    ->group(function (): void {
        Route::get('challenge', 'challenge')
            ->middleware('throttle:'.config('captcha.throttle.challenge', '40,1').',captcha-challenge')
            ->name('challenge');

        Route::post('verify', 'verify')
            ->middleware('throttle:'.config('captcha.throttle.verify', '40,1').',captcha-verify')
            ->name('verify');

        Route::get('asset/{file}', 'asset')->whereIn('file', Asset::FILES)->name('asset');
    });
