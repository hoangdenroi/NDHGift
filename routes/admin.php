<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    // Trang Dashboard quản trị hệ thống
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
});
