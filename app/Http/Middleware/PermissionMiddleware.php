<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user === null) {
            return $this->deny();
        }

        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $permission = $routeName && str_starts_with($routeName, 'admin.')
            ? substr($routeName, strlen('admin.'))
            : null;

        try {
            if ($permission && $user->hasPermissionTo($permission)) {
                return $next($request);
            }
        } catch (PermissionDoesNotExist) {
            return $this->deny();
        }

        return $this->deny();
    }

    private function deny(): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu işlem için yetkiniz yok.',
            ], 403);
        }

        abort(403, 'Bu işlemi yapma yetkiniz yok.');
    }
}
