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

                return redirect()->route('login')->with([
                    'toast_message' => 'Tài khoản của bạn đã bị khóa hoặc không hoạt động. Vui lòng liên hệ quản trị viên.',
                    'toast_type' => 'error',
                ]);
            }
        }

        return $next($request);
    }
}
