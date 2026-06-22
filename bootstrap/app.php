<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cấu hình Trust Proxies cho Cloudflare / Reverse Proxy
        $middleware->trustProxies(at: '*');

        // Đăng ký middleware alias cho hệ thống đa ngôn ngữ và phân quyền
        $middleware->alias([
            'set.locale' => \App\Http\Middleware\SetLocale::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Khi user chưa đăng nhập, redirect về trang login kèm locale prefix + query param thông báo
        $middleware->redirectGuestsTo(function () {
            $locale = session('locale', config('localization.default_locale', 'en'));

            return route('login', ['locale' => $locale, 'auth_required' => 1]);
        });

        // Web middleware stack — thêm Security Headers + kiểm tra tài khoản + throttle
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':web',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
