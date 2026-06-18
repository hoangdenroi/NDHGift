<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware xử lý đa ngôn ngữ qua URL prefix.
 *
 * Đọc segment {locale} từ URL, validate xem có nằm trong danh sách
 * ngôn ngữ hỗ trợ hay không. Nếu hợp lệ → setLocale cho toàn ứng dụng.
 * Nếu không hợp lệ → redirect về locale mặc định.
 *
 * Đồng thời inject `locale` vào URL defaults để route() helper
 * tự động gắn locale prefix vào tất cả URL sinh ra.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var string|null $locale */
        $locale = $request->route('locale');

        $supportedLocales = config('localization.supported_locales', ['en', 'vi']);
        $defaultLocale = config('localization.default_locale', 'en');

        // Validate: chỉ chấp nhận locale nằm trong whitelist
        if (!in_array($locale, $supportedLocales, true)) {
            // Loại bỏ segment locale không hợp lệ, giữ lại phần path còn lại
            $path = $request->path();
            $segments = explode('/', $path);

            // Bỏ segment đầu tiên (locale sai)
            if (count($segments) > 1) {
                array_shift($segments);
                $remainingPath = implode('/', $segments);
            } else {
                $remainingPath = '';
            }

            return redirect()->to("/{$defaultLocale}/{$remainingPath}");
        }

        // Thiết lập locale cho toàn ứng dụng
        App::setLocale($locale);

        // Lưu vào session để ghi nhớ lựa chọn của user
        session(['locale' => $locale]);

        // Inject locale vào URL defaults — để route() helper tự động gắn locale prefix
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
