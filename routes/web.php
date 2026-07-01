<?php

declare(strict_types=1);

use App\Http\Controllers\App\about\AboutController;
use App\Http\Controllers\App\billing\BillingController;
use App\Http\Controllers\App\coupon\CouponController;
use App\Http\Controllers\App\gift\GiftController;
use App\Http\Controllers\App\history\HistoryController;
use App\Http\Controllers\App\home\HomeController;
use App\Http\Controllers\App\notification\NotificationController;
use App\Http\Controllers\App\profile\ProfileController;
use App\Http\Controllers\App\support\SupportController;
use App\Http\Controllers\App\topup\TopupController;
use App\Http\Controllers\Auth\SocialiteController;
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
        Route::get('/share', [HomeController::class, 'share'])->name('affiliate.share');

        Route::prefix('apps')->group(function () {
            Route::get('/gift', [GiftController::class, 'index'])->name('app.gift.index');

            Route::get('/support', [SupportController::class, 'index'])->name('app.support.index');

            Route::get('/profile', [ProfileController::class, 'index'])->name('app.profile.index');

            Route::get('/about', [AboutController::class, 'index'])->name('app.about.index');

        });

        Route::middleware('auth')->prefix('apps')->group(function () {
            // Các route yêu cầu đăng nhập sẽ được thêm tại đây
            Route::post('/profile', [ProfileController::class, 'update'])->name('app.profile.update');

            // --- QUÀ TẶNG (GIFT) — Trang chỉnh sửa nội dung trước khi thanh toán ---
            Route::get('/gift/{giftTemplate}/create', [GiftController::class, 'create'])->name('app.gift.create');

            // --- NẠP TIỀN (TOPUP) ---
            Route::get('/topup', [TopupController::class, 'index'])->name('app.topup');

            // --- HÓA ĐƠN & THANH TOÁN (BILLING) ---
            Route::get('/billing', [BillingController::class, 'index'])->name('app.billing');

            // --- MÃ GIẢM GIÁ (COUPON) ---
            Route::post('/coupon/redeem', [CouponController::class, 'redeem'])->name('app.coupon.redeem');
            Route::post('/api/apply-coupon', [CouponController::class, 'applyCoupon'])->name('api.apply-coupon');

            // --- LỊCH SỬ (HISTORY) ---
            Route::get('/history', [HistoryController::class, 'index'])->name('app.history.index');
        });
    });

// --- API dùng Session Auth (không bị ảnh hưởng bởi locale prefix) ---
Route::middleware('auth')->prefix('api')->group(function () {
    Route::post('v1/settings', [ProfileController::class, 'updateSettings'])->name('api.settings.update');
    Route::get('v1/profile/xp-transactions', [ProfileController::class, 'xpTransactions'])->name('api.profile.xp_transactions');
    Route::get('v1/profile/leaderboard', [ProfileController::class, 'leaderboard'])->name('api.profile.leaderboard');
    Route::post('v1/profile/toggle-anonymous', [ProfileController::class, 'toggleAnonymous'])->name('api.profile.toggle_anonymous');
    Route::post('v1/profile/send-verification', [ProfileController::class, 'sendVerificationEmail'])->name('api.profile.send_verification');
    Route::post('v1/profile/quests/claim', [ProfileController::class, 'claimQuestXp'])->name('api.profile.claim_quest');
    Route::post('v1/profile/email/send-otp', [ProfileController::class, 'sendEmailChangeOtp'])->name('api.profile.send_email_otp');
    Route::post('v1/profile/email/confirm-change', [ProfileController::class, 'confirmEmailChange'])->name('api.profile.confirm_email_change');

    // --- NẠP TIỀN API (Khớp với topup-index.blade.php) ---
    Route::post('v1/topup/create', [TopupController::class, 'createTopup'])->name('api.topup.create');
    Route::post('v1/topup/{transaction}/cancel', [TopupController::class, 'cancelTopup'])->name('api.topup.cancel');
    Route::get('v1/topup/pending', [TopupController::class, 'pendingTransactions'])->name('api.topup.pending');
    Route::get('v1/topup/history', [TopupController::class, 'history'])->name('api.topup.history');

    // --- THÔNG BÁO API ---
    Route::get('v1/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('v1/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('v1/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read_all');
    Route::post('v1/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('api.notifications.clear_all');
});

// --- Social Login (Google & Facebook) ---
Route::get('/auth/{provider}', [SocialiteController::class, 'redirect'])
    ->middleware('throttle:auth')
    ->name('social.login')
    ->where('provider', 'google|facebook');

Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
    ->where('provider', 'google|facebook');

require __DIR__.'/auth.php';
