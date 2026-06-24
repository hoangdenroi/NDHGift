<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AuditLogService;
use App\View\Components\AppLayout;
use App\View\Components\AuthLayout;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đăng ký các layout components
        Blade::component('app-layout', AppLayout::class);
        Blade::component('auth-layout', AuthLayout::class);

        // Cấu hình Carbon hiển thị tiếng Việt (diffForHumans, v.v.)
        Carbon::setLocale('vi');

        // Laravel HTTP Client Timeout (outgoing request): 120 giây mặc định
        Http::globalOptions(['timeout' => 120]);

        // Đăng ký Blueprint macro — dùng $table->baseColumns() trong migration
        Blueprint::macro('baseColumns', function () {
            $this->id();
            $this->ulid('unitcode')->unique();
            $this->json('metadata')->nullable();
            $this->boolean('is_deleted')->default(false)->index();
            $this->dateTime('deleted_at')->nullable();
            $this->timestamps();
        });

        // === RATE LIMITING ===

        // Giới hạn tổng request web: 100 req/phút — chống DDoS tầng ứng dụng
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(100)->by($request->session()?->get('_token') ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? 60;

                    return response()->view('errors.429', [
                        'message' => __('The system has detected that your request frequency is too fast. Please take a short break.'),
                        'seconds' => $retryAfter,
                    ], 429)->withHeaders($headers);
                });
        });

        // Giới hạn xác thực: 5 req/phút — chống brute-force login/register/reset password
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip())
                ->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? 60;

                    return response()->view('errors.429', [
                        'message' => __('You have made too many authentication attempts. For security, the system will temporarily block this request.'),
                        'seconds' => $retryAfter,
                    ], 429)->withHeaders($headers);
                });
        });

        // === AUDIT LOG — Theo dõi Login/Logout ===

        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;
            if ($user) {
                // 1. Ghi log audit hoạt động login
                AuditLogService::log('login', $user, null, null, $user->id);

                // 2. Tự động set cookie ref_tracker lưu mã giới thiệu của thiết bị này
                cookie()->queue('ref_tracker', $user->affiliate_code, 60 * 24 * 365);

                // 3. Lưu IP đăng nhập hiện tại vào metadata để chống gian lận affiliate
                $request = request();
                if ($request) {
                    $currentIp = $request->ip();
                    if ($currentIp) {
                        $metadata = $user->metadata ?? [];
                        $recentIps = $metadata['recent_ips'] ?? [];

                        if (!in_array($currentIp, $recentIps, true)) {
                            $recentIps[] = $currentIp;
                            // Giới hạn lưu tối đa 10 IP gần nhất để tránh phình dung lượng data
                            if (count($recentIps) > 10) {
                                array_shift($recentIps);
                            }
                            $metadata['recent_ips'] = $recentIps;
                            $user->metadata = $metadata;
                            $user->save();
                        }
                    }
                }
            }
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuditLogService::log('logout', $event->user, null, null, $event->user->id);
            }
        });
    }
}
