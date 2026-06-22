<?php

declare(strict_types=1);

use App\Http\Controllers\App\topup\TopupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Nơi định nghĩa các API routes của ứng dụng. Các route này được bọc
| trong cấu hình bảo mật và không bị ảnh hưởng bởi locale prefix.
|
*/

// --- Webhook SePay nhận thông báo nạp tiền tự động ---
Route::post('/v1/topup/sepay-hook', [TopupController::class, 'sepayHook'])->name('api.sepay-hook');
