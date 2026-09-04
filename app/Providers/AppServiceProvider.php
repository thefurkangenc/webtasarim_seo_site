<?php

namespace App\Providers;

use App\Services\Setting\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // super-admin her izne sahiptir; izin listesi senkronlanmaz.
        Gate::before(fn ($user) => $user->hasRole('super-admin') ? true : null);

        // <x-admin::form.input /> gibi bileşenler resources/views/admin/components altında.
        Blade::anonymousComponentPath(resource_path('views/admin/components'), 'admin');

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
