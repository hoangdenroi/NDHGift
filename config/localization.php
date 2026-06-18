<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Ngôn ngữ mặc định
    |--------------------------------------------------------------------------
    |
    | Khi user truy cập "/" sẽ redirect về /{default_locale}/
    | Sử dụng mã ISO 639-1 (2 ký tự).
    |
    */
    'default_locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Danh sách ngôn ngữ hỗ trợ
    |--------------------------------------------------------------------------
    |
    | Chỉ các locale có trong danh sách này mới được chấp nhận.
    | Thiết kế mở rộng — dễ dàng thêm ngôn ngữ mới bằng cách
    | thêm phần tử vào mảng và tạo file dịch tương ứng.
    |
    */
    'supported_locales' => ['en', 'vi'],

    /*
    |--------------------------------------------------------------------------
    | Nhãn hiển thị cho từng ngôn ngữ
    |--------------------------------------------------------------------------
    |
    | Dùng cho Language Switcher UI — hiển thị tên ngôn ngữ
    | dạng thân thiện với người dùng.
    |
    */
    'locale_labels' => [
        'en' => 'English',
        'vi' => 'Tiếng Việt',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cờ quốc gia (emoji) cho từng ngôn ngữ
    |--------------------------------------------------------------------------
    |
    | Dùng cho Language Switcher UI — hiển thị cờ quốc gia
    | kèm theo tên ngôn ngữ.
    |
    */
    'locale_flags' => [
        'en' => '🇺🇸',
        'vi' => '🇻🇳',
    ],

];
