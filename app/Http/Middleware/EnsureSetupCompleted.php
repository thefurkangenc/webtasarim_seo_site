<?php

namespace App\Http\Middleware;

use App\Services\Setup\SetupService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kurulum bitmeden site ve panel kapalıdır; her adres sihirbaza düşer.
 * Mevcut (sihirbazdan önce açılmış) kurulumlar kullanıcı varsa tamamlanmış sayılır.
 */
class EnsureSetupCompleted
{
    public function __construct(private readonly SetupService $setup) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('up')) {
            return $next($request);
        }

        if ($this->setup->isComplete()) {
            return $this->leaveWizard($request, $next);
        }

        $inProgress = $request->session()->has('setup');

        if ($this->setup->hasUsers() && ! $inProgress) {
            $this->setup->markComplete();

            return $this->leaveWizard($request, $next);
        }

        if ($request->routeIs('setup.*')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Kurulum tamamlanmadan bu işlem yapılamaz.',
            ], 403);
        }

        return redirect()->route('setup.index');
    }

    private function leaveWizard(Request $request, Closure $next): Response
    {
        if (! $request->routeIs('setup.*')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Kurulum zaten tamamlandı.',
            ], 403);
        }

        return redirect()->route($request->user() ? 'admin.dashboard' : 'admin.login');
    }
}
