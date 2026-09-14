<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pasif bırakılmış bir hesap oturum açmış kalsa bile panele giremez.
 * Yönetici hesabı kapattığında açık oturum bir sonraki istekte düşer.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hesabınız pasif duruma alındı.',
                ], 403);
            }

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => 'Bu hesap pasif durumda. Yöneticinizle görüşün.']);
        }

        return $next($request);
    }
}
