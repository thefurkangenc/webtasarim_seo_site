<?php

namespace App\Captcha;

use App\Captcha\View\CaptchaComponent;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Captcha altyapısını uygulamaya bağlar.
 *
 * Kurulum (başka bir projeye taşırken yapılacak İKİ satır):
 *
 *   1) bootstrap/providers.php'ye  App\Captcha\CaptchaServiceProvider::class
 *   2) bootstrap/app.php > withRouting(then:) içine, catch-all route'lardan ÖNCE
 *      Route::middleware('web')->group(base_path('app/Captcha/routes.php'));
 *
 * Gerisi bu klasörün içindedir.
 */
class CaptchaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
        | mergeConfigFrom bilinçli kullanılmadı: `php artisan config:cache`
        | çalıştırıldığında Laravel birleştirmeyi atlıyor ve config/ altında
        | karşılığı olmayan bu ayarlar tamamen kayboluyor. Burada her istekte
        | dosyadan okunur, varsa config/captcha.php üstte kalır.
        */
        config()->set('captcha', array_replace_recursive(
            require __DIR__.'/config.php',
            (array) config('captcha', []),
        ));

        $this->app->singleton(CaptchaManager::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'captcha');

        Blade::component(CaptchaComponent::class, 'captcha');
    }
}
