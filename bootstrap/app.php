<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cấu hình Trust Proxies cho Cloudflare / Reverse Proxy
        $middleware->trustProxies(at: '*');

        // Khi user chưa đăng nhập, redirect về trang login kèm query parameter để hiển thị thông báo
        $middleware->redirectGuestsTo(function () {
            return route('login', ['auth_required' => 1]);
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
