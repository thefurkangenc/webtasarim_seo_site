<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
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
    }
}
