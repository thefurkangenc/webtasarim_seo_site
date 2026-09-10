<?php

use App\Http\Middleware\EnsureSiteIsLive;
use App\Http\Middleware\PermissionMiddleware as MiddlewarePermissionMiddleware;
use App\Services\Redirect\NotFoundLogger;
use App\Services\Redirect\RedirectResolver;
use App\Support\Activity;
use App\Support\Consent;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // Dinamik sayfaların catch-all'ı: her şeyi yakaladığı için
            // uygulamanın EN SON route'u olmak zorunda. Yeni bir route grubu
            // eklenirse bu satırın ÜSTÜNE eklenir.
            Route::middleware('web')->group(base_path('routes/pages.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // auth middleware'i misafirleri admin girişine yollar.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));

        $middleware->web(append: [
            EnsureSiteIsLive::class,
        ]);

        $middleware->encryptCookies(except: [
            Consent::COOKIE,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'permission_middleware' => MiddlewarePermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Servislerin fırlattığı iş kuralı hataları JSON sözleşmesine çevrilir.
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
        });

        // FormRequest::authorize() false döndüğünde.
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            Activity::forbidden($request);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz yok.'], 403);
            }
        });

        // permission / role middleware reddettiğinde.
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            Activity::forbidden($request);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz yok.'], 403);
            }
        });

        /*
        | Ön yüzde bir adres hiçbir route'a denk gelmediğinde (ya da bir
        | controller abort(404) attığında): önce yönlendirme yöneticisine
        | sorulur, eşleşme yoksa 404 kaydına işlenip varsayılan 404'e bırakılır.
        |
        | Yalnızca ön yüz GET/HEAD istekleri: panel, API ve JSON istekleri ile
        | yazma metotları dokunulmadan varsayılan davranışa gider.
        */
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
                return null;
            }

            if ($request->expectsJson() || $request->is('admin', 'admin/*', 'api/*')) {
                return null;
            }

            $result = app(RedirectResolver::class)->resolve($request->path());

            if ($result) {
                app(RedirectResolver::class)->registerHit($result->redirectId);

                return $result->toResponse();
            }

            app(NotFoundLogger::class)->record($request);

            return null;
        });
    })->create();
