<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Coupon\CouponController;
use App\Http\Controllers\Admin\dashboard\DashboardController;
use App\Http\Controllers\Admin\Notification\NotificationController;
use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    // Trang Dashboard quản trị hệ thống
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // === QUẢN LÝ NGƯỜI DÙNG ===
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit'])->names('admin.users');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');

    // === QUẢN LÝ MÃ GIẢM GIÁ ===
    Route::resource('coupons', CouponController::class)->except(['create', 'show', 'edit'])->names('admin.coupons');
    Route::patch('coupons/{coupon}/toggle-active', [CouponController::class, 'toggleActive'])->name('admin.coupons.toggle-active');

    // === QUẢN LÝ THÔNG BÁO (giao diện tĩnh) ===
    Route::get('notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
});
