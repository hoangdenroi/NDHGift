<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\home;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('components.pages.app.home.home-index');
    }

    /**
     * Xử lý liên kết chia sẻ giới thiệu, lưu mã ref vào session/cookie và redirect về trang chủ.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function share(Request $request, string $locale): RedirectResponse
    {
        $ref = $request->query('ref');

        if ($ref) {
            // Xác thực xem mã affiliate có thực sự tồn tại trong hệ thống hay không
            $exists = User::where('affiliate_code', $ref)->exists();
            if ($exists) {
                // Lưu trữ mã giới thiệu vào Session
                session(['affiliate_ref' => $ref]);

                // Đồng thời lưu vào Cookie dài hạn (30 ngày) phòng trường hợp Session hết hạn
                cookie()->queue('affiliate_ref', $ref, 60 * 24 * 30);
            }
        }

        return redirect()->route('app.home.index', ['locale' => $locale]);
    }
}