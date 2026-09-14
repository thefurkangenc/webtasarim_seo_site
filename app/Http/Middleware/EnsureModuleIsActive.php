<?php

namespace App\Http\Middleware;

use App\Support\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PermissionMiddleware'in desenini izler: pasif bir modülün route'una
 * doğrudan adres yazılırsa JSON isteğe JSON, normal isteğe abort().
 *
 * Durum kodu parametreyle verilir çünkü iki tarafın doğru cevabı farklı:
 * panelde 403 ("modül pasif, yöneticinin haberi olsun"), ön yüzde 404
 * (kullanılmayan bir modülün adresi ziyaretçi için hiç yoktur).
 *
 *   module.active:project       -> admin, 403
 *   module.active:project,404   -> ön yüz, 404
 */
class EnsureModuleIsActive
{
    public function __construct(private readonly ModuleRegistry $modules) {}

    public function handle(Request $request, Closure $next, string $key, int|string $status = 403): Response
    {
        if ($this->modules->isActive($key)) {
            return $next($request);
        }

        $status = (int) $status;
        $message = $status === 404 ? 'Sayfa bulunamadı.' : 'Bu modül şu an pasif.';

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        abort($status, $message);
    }
}
