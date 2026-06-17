<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware thiết lập các header bảo mật chuẩn OWASP.
 * Áp dụng cho toàn bộ request để giảm thiểu
 * các cuộc tấn công phổ biến: Clickjacking, MIME sniffing, v.v.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Chống Clickjacking — không cho phép nhúng trang vào iframe
        $response->headers->set('X-Frame-Options', 'DENY');

        // Chống MIME sniffing — trình duyệt phải tuân thủ Content-Type
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Phòng thủ XSS cho trình duyệt cũ (IE, Safari legacy)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Kiểm soát thông tin referrer gửi đi khi chuyển trang
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Hạn chế quyền truy cập API thiết bị (camera, micro, GPS)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Ép buộc HTTPS trong 1 năm — chỉ áp dụng môi trường production
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
