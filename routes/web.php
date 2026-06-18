<?php

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
        Route::get('/', function () {
            return view('components.pages.app.home.home-index');
        })->name('home');

        Route::middleware('auth')->group(function () {
            // Các route yêu cầu đăng nhập sẽ được thêm tại đây
        });
    });

require __DIR__.'/auth.php';
