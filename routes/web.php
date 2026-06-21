<?php

declare(strict_types=1);

use App\Http\Controllers\App\about\AboutController;
use App\Http\Controllers\App\gift\GiftController;
use App\Http\Controllers\App\home\HomeController;
use App\Http\Controllers\App\profile\ProfileController;
use App\Http\Controllers\App\support\SupportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route gốc — Redirect về locale mặc định
|--------------------------------------------------------------------------
|
| Khi user truy cập "/" sẽ tự động redirect về /{default_locale}/
| Ưu tiên locale đã lưu trong session, fallback về config mặc định.
|
*/
Route::get('/', function () {
    $locale = session('locale', config('localization.default_locale', 'en'));

    return redirect()->to("/{$locale}");
});

/*
|--------------------------------------------------------------------------
| Routes có locale prefix — /vi/... hoặc /en/...
|--------------------------------------------------------------------------
|
| Tất cả routes giao diện user được bọc trong group có prefix {locale}.
| Middleware 'set.locale' xử lý validate locale + setLocale cho app.
| Constraint 'where' đảm bảo chỉ chấp nhận mã ISO 639-1 (2 ký tự chữ cái).
|
*/
Route::prefix('{locale}')
    ->where(['locale' => '[a-z]{2}'])
    ->middleware('set.locale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('app.home.index');

        Route::prefix('apps')->group(function () {
            Route::get('/gift', [GiftController::class, 'index'])->name('app.gift.index');

            Route::get('/support', [SupportController::class, 'index'])->name('app.support.index');

            Route::get('/profile', [ProfileController::class, 'index'])->name('app.profile.index');

            Route::get('/about', [AboutController::class, 'index'])->name('app.about.index');

        });

        Route::middleware('auth')->prefix('apps')->group(function () {
            // Các route yêu cầu đăng nhập sẽ được thêm tại đây
            Route::post('/profile', [ProfileController::class, 'update'])->name('app.profile.update');
        });
    });

// --- API dùng Session Auth (không bị ảnh hưởng bởi locale prefix) ---
Route::middleware('auth')->prefix('api')->group(function () {
    Route::post('v1/settings', [ProfileController::class, 'updateSettings'])->name('api.settings.update');
});

require __DIR__.'/auth.php';
