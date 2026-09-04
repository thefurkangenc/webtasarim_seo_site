<?php

namespace App\Http\Middleware;

use App\Services\Maintenance\MaintenanceService;
use App\Support\Settings;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsLive
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $enabled = Settings::bool('maintenance.enabled');
        } catch (QueryException) {
            return $next($request);
        }

        if (! $enabled || $this->exempt($request)) {
            return $next($request);
        }

        return app(MaintenanceService::class)->response();
    }

    private function exempt(Request $request): bool
    {
        if ($request->is('admin', 'admin/*')) {
            return true;
        }

        if ($request->routeIs('maintenance.preview', 'maintenance.bypass')) {
            return true;
        }

        if ($request->user()) {
            return true;
        }

        $secret = (string) (Settings::get('maintenance.bypass_secret') ?? '');

        if ($secret === '') {
            return false;
        }

        $cookie = (string) $request->cookie('maintenance_bypass', '');

        return $cookie !== '' && hash_equals(hash('sha256', $secret), $cookie);
    }
}
