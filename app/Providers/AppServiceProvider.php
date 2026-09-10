<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Models\Menu\Menu;
use App\Models\Menu\MenuItem;
use App\Observers\MenuObserver;
use App\Observers\RedirectObserver;
use App\Services\ActivityLog\ActivityLogger;
use App\Services\Setting\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton: istek bağlamı (user-agent ayrıştırması, IP, request_id)
        // istek başına bir kez hesaplanıp o istekteki tüm loglarda paylaşılır.
        $this->app->singleton(ActivityLogger::class);
    }

    public function boot(): void
    {
        // super-admin her izne sahiptir; izin listesi senkronlanmaz.
        Gate::before(fn ($user) => $user->hasRole('super-admin') ? true : null);

        // Giriş/çıkış/başarısız giriş denetim kaydına yazılır. Olay keşfine
        // güvenmek yerine açıkça kaydediliyor.
        Event::subscribe(LogAuthenticationActivity::class);

        // <x-admin::form.input /> gibi bileşenler resources/views/admin/components altında.
        Blade::anonymousComponentPath(resource_path('views/admin/components'), 'admin');

        // Menü ya da öğe değişince ön yüz menü önbelleğini temizle.
        Menu::observe(MenuObserver::class);
        MenuItem::observe(MenuObserver::class);

        // Adresi değişen kayıtlar için otomatik 301 (Page, Service...).
        foreach (config('redirects.auto_from', []) as $model) {
            $model::observe(RedirectObserver::class);
        }

        $this->app->booted(function () {
            $this->app->make(SettingService::class)->applyMailConfig();
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Çok fazla deneme yaptınız. Lütfen bir dakika bekleyin.',
                ], 429);
            });
        });
    }
}
