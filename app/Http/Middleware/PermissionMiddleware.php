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
            return abort(403, 'Bu işlemi yapma yetkiniz yok.');
        }

        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        $routeName = $request->route()->getName();

        try {
            if ($user->hasPermissionTo($routeName)) {
                return $next($request);
            }
        } catch (PermissionDoesNotExist) {
            return abort(403, 'Bu işlemi yapma yetkiniz yok.');
        }

        return abort(403, 'Bu işlemi yapma yetkiniz yok.');
    }
}
