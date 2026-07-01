<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\gift;

use App\Http\Controllers\Controller;
use App\Models\UserGift;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class UserGiftController extends Controller
{
    /**
     * Trình phát quà tặng dựa trên slug công khai.
     *
     * @param string $slug
     * @return View|Response
     */
    public function play(string $slug)
    {
        // 1. Tìm quà tặng theo slug
        $userGift = UserGift::where('slug', $slug)
            ->where('is_deleted', false)
            ->firstOrFail();

        // 2. Kiểm tra trạng thái thanh toán
        if ($userGift->status !== UserGift::STATUS_PAID) {
            abort(404, __('Quà tặng này chưa được kích hoạt.'));
        }

        // 3. Kiểm tra thời hạn hiệu lực (hết hạn)
        if ($userGift->expires_at !== null && $userGift->expires_at->isPast()) {
            return response()->view('errors.gift-expired', [
                'userGift' => $userGift
            ], 403);
        }

        // 4. Kiểm tra hẹn giờ gửi quà (chưa đến giờ mở khóa)
        if ($userGift->isScheduledAndWaiting()) {
            return view('components.pages.app.gift.gift-countdown', [
                'userGift' => $userGift
            ]);
        }

        // 5. Quà tặng hợp lệ -> Tăng lượt xem
        $userGift->incrementView();

        // 6. Lấy template tương ứng và render
        $template = $userGift->template;
        $giftData = $userGift->content_data;

        // Fallback template_code nếu file view không tồn tại
        $viewPath = "gifts.templates.{$template->code}";
        if (! view()->exists($viewPath)) {
            $viewPath = 'gifts.templates.heart_3d';
        }

        return view($viewPath, [
            'giftData' => $giftData,
            'giftTemplate' => $template,
            'isDemo' => false
        ]);
    }
}
