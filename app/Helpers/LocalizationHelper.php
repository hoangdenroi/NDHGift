<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\App;

/**
 * Helper class hỗ trợ xử lý đa ngôn ngữ.
 *
 * Tập trung các phương thức tiện ích liên quan đến localization
 * để tránh rải rác logic locale khắp codebase.
 */
class LocalizationHelper
{
    /**
     * Sinh URL cho route có locale prefix.
     *
     * Tự động gắn locale hiện tại nếu không truyền vào,
     * đảm bảo tất cả URL sinh ra đều có prefix ngôn ngữ.
     */
    public static function localizedRoute(string $name, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();

        return route($name, array_merge(['locale' => $locale], $parameters));
    }

    /**
     * Chuyển đổi URL hiện tại sang ngôn ngữ khác.
     *
     * Thay thế segment locale trong URL hiện tại,
     * giữ nguyên toàn bộ path và query string.
     * Dùng cho Language Switcher.
     */
    public static function switchLocaleUrl(string $targetLocale): string
    {
        $currentLocale = App::getLocale();
        $currentUrl = url()->current();

        // Thay thế segment locale đầu tiên trong URL
        $baseUrl = url('/');

        // Lấy path sau base URL
        $path = str_replace($baseUrl, '', $currentUrl);

        // Thay thế locale prefix
        $newPath = preg_replace(
            '#^/' . preg_quote($currentLocale, '#') . '(/|$)#',
            '/' . $targetLocale . '$1',
            $path
        );

        // Giữ lại query string nếu có
        $queryString = request()->getQueryString();

        return $baseUrl . $newPath . ($queryString ? '?' . $queryString : '');
    }

    /**
     * Lấy danh sách ngôn ngữ hỗ trợ kèm label hiển thị.
     *
     * Trả về mảng locale => label để dùng trong Language Switcher dropdown.
     */
    public static function getSupportedLocales(): array
    {
        return config('localization.locale_labels', []);
    }

    /**
     * Lấy danh sách cờ quốc gia cho ngôn ngữ.
     */
    public static function getLocaleFlags(): array
    {
        return config('localization.locale_flags', []);
    }

    /**
     * Kiểm tra locale có được hỗ trợ hay không.
     */
    public static function isSupported(string $locale): bool
    {
        return in_array($locale, config('localization.supported_locales', []), true);
    }
}
