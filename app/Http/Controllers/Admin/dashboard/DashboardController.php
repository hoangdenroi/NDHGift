<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Hiển thị trang quản trị Dashboard chính của hệ thống.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        return view('components.pages.admin.dashboard.dashboard');
    }
}
