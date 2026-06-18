<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kiểm tra trạng thái tài khoản trên mỗi request.
 * Nếu tài khoản bị khóa (status !== 'active') hoặc đã xóa mềm (is_deleted = true)
 * → tự động logout, hủy session, chuyển về trang login.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Tài khoản bị khóa hoặc đã xóa → ép logout ngay lập tức
            if ($user->status !== 'active' || $user->is_deleted) {
                Auth::guard('web')->logout();

                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                // Xác định locale từ segment đầu tiên của URL để redirect đúng ngôn ngữ
                $segmentLocale = $request->segment(1);
                $supportedLocales = config('localization.supported_locales', ['en', 'vi']);
                $locale = in_array($segmentLocale, $supportedLocales, true)
                    ? $segmentLocale
                    : session('locale', config('localization.default_locale', 'en'));

                return redirect()->route('login', ['locale' => $locale])->with([
                    'toast_message' => __('Your account has been locked or is inactive. Please contact the administrator.'),
                    'toast_type' => 'error',
                ]);
            }
        }

        return $next($request);
    }
}
