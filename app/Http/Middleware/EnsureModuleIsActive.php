<?php

namespace App\Http\Middleware;

use App\Support\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PermissionMiddleware'in desenini izler: pasif bir modülün route'una
 * doğrudan adres yazılırsa JSON isteğe 403 JSON, normal isteğe abort(403).
 */
class EnsureModuleIsActive
{
    public function __construct(private readonly ModuleRegistry $modules) {}

    public function handle(Request $request, Closure $next, string $key): Response
    {
        if ($this->modules->isActive($key)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Bu modül şu an pasif.'], 403);
        }

        abort(403, 'Bu modül şu an pasif.');
    }
}
