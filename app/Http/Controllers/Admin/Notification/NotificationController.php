<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Controller quản lý thông báo phía Admin.
 *
 * Giai đoạn hiện tại: chỉ render giao diện tĩnh, logic nghiệp vụ sẽ triển khai sau.
 */
class NotificationController extends Controller
{
    /**
     * Hiển thị trang quản lý thông báo (giao diện tĩnh).
     */
    public function index(): View
    {
        return view('components.pages.admin.notifications.notification-index');
    }
}
