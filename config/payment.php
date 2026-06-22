<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Cấu hình thanh toán VietQR & SePay
    |--------------------------------------------------------------------------
    |
    | Định nghĩa các thông số tài khoản ngân hàng nhận tiền tự động và
    | API Key xác thực Webhook gửi về từ SePay.
    |
    */

    'vietqr' => [
        'bin' => env('VIETQR_BIN', '970423'),
        'account' => env('VIETQR_ACCOUNT', '10003179213'),
        'name' => env('VIETQR_NAME', 'NGUYEN DUC HOANG'),
        'template' => env('VIETQR_TEMPLATE', 'compact'),
        'prefix' => env('VIETQR_PREFIX', 'SEVQR '),
    ],

    'sepay' => [
        'api_key' => env('SEPAY_API_KEY', 'sepay_api_key_default'),
    ],
];
