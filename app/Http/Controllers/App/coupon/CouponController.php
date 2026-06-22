<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\coupon;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller CouponController
 *
 * Xử lý các request liên quan đến áp dụng mã giảm giá khi thanh toán và quy đổi mã quà tặng thành số dư.
 */
class CouponController extends Controller
{
    /**
     * Áp dụng mã giảm giá cho đơn hàng thanh toán.
     *
     * @param Request $request
     * @param CouponService $couponService
     * @return JsonResponse
     */
    public function applyCoupon(Request $request, CouponService $couponService): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
        ]);

        try {
            $result = $couponService->applyCoupon(
                $request->input('code'),
                (float) $request->input('subtotal'),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Áp dụng mã giảm giá thành công!',
                'discount' => $result['discount_amount'],
                'coupon_code' => $result['coupon_code'],
                'type' => $result['type'],
                'value' => $result['value'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Quy đổi mã quà tặng/mã giảm giá cố định thành số dư ví.
     *
     * @param Request $request
     * @param CouponService $couponService
     * @return JsonResponse
     */
    public function redeem(Request $request, CouponService $couponService): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $user = $request->user();

        try {
            $redeemedAmount = $couponService->redeemCoupon(
                $request->input('code'),
                $user
            );

            // Fresh lại user để lấy số dư mới
            $freshUser = $user->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Quy đổi thành công! Đã cộng ' . number_format($redeemedAmount, 0, ',', '.') . 'đ vào tài khoản.',
                'new_balance' => number_format((float) $freshUser->balance, 0, ',', '.') . 'đ',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Quy đổi coupon thất bại', [
                'user_id' => $user->id,
                'code' => $request->input('code'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
