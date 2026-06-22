<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Coupon;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service CouponService
 *
 * Chứa logic nghiệp vụ liên quan đến việc kiểm tra, áp dụng và quy đổi mã giảm giá/mã quà tặng.
 */
class CouponService
{
    /**
     * Xác thực tính hợp lệ của một mã giảm giá.
     *
     * @param string $code Mã giảm giá
     * @param float $orderTotal Tổng tiền đơn hàng
     * @param User|null $user Người dùng áp dụng (nếu có)
     * @return Coupon
     * @throws Exception
     */
    public function validateCoupon(string $code, float $orderTotal = 0, ?User $user = null): Coupon
    {
        $code = strtoupper(trim($code));
        $coupon = Coupon::where('code', $code)->first();

        // 1. Kiểm tra tồn tại
        if (!$coupon) {
            throw new Exception('Mã giảm giá không tồn tại trên hệ thống.');
        }

        // 2. Kiểm tra tính hợp lệ chung (is_active, thời gian, lượt dùng tổng)
        if (!$coupon->isValid($orderTotal)) {
            if (!$coupon->is_active) {
                throw new Exception('Mã giảm giá hiện tại không hoạt động.');
            }
            if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
                throw new Exception('Mã giảm giá chưa đến thời gian áp dụng.');
            }
            if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
                throw new Exception('Mã giảm giá đã hết hạn sử dụng.');
            }
            if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
                throw new Exception('Mã giảm giá đã hết lượt sử dụng trên toàn hệ thống.');
            }
            if ($orderTotal < (float) $coupon->min_order) {
                throw new Exception('Đơn hàng tối thiểu ' . number_format((float) $coupon->min_order, 0, ',', '.') . 'đ để áp dụng mã này.');
            }
            throw new Exception('Mã giảm giá không hợp lệ.');
        }

        // 3. Kiểm tra xem người dùng đã dùng mã này trước đó chưa (nếu có truyền user)
        if ($user) {
            $alreadyUsed = $user->coupons()->where('coupon_id', $coupon->id)->exists();
            if ($alreadyUsed) {
                throw new Exception('Bạn đã sử dụng mã giảm giá này rồi. Mỗi tài khoản chỉ được dùng 1 lần.');
            }
        }

        return $coupon;
    }

    /**
     * Áp dụng mã giảm giá cho một đơn hàng (chỉ tính toán, không lưu DB).
     *
     * @param string $code Mã giảm giá
     * @param float $orderTotal Tổng tiền đơn hàng
     * @param User $user Người dùng áp dụng
     * @return array Thông tin giảm giá tính toán được
     * @throws Exception
     */
    public function applyCoupon(string $code, float $orderTotal, User $user): array
    {
        $coupon = $this->validateCoupon($code, $orderTotal, $user);
        $discountAmount = $coupon->calculateDiscount($orderTotal);

        return [
            'coupon_code' => $coupon->code,
            'discount_amount' => $discountAmount,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
        ];
    }

    /**
     * Quy đổi mã giảm giá cố định (fixed) thành số dư tài khoản người dùng.
     * Sử dụng Database Transaction và Pessimistic Locking để chống lỗi Race Condition (Double Spending).
     *
     * @param string $code Mã giảm giá
     * @param User $user Người dùng thực hiện quy đổi
     * @return float Số tiền được cộng vào tài khoản
     * @throws Exception
     */
    public function redeemCoupon(string $code, User $user): float
    {
        // Kiểm tra hợp lệ cơ bản trước khi mở transaction
        $coupon = $this->validateCoupon($code, 0, $user);

        // Chỉ cho phép quy đổi mã giảm tiền cố định (fixed) thành số dư
        if ($coupon->type !== 'fixed') {
            throw new Exception('Mã giảm giá dạng phần trăm chỉ được áp dụng khi thanh toán đơn hàng.');
        }

        $redeemAmount = (float) $coupon->value;

        DB::transaction(function () use ($user, $coupon, $redeemAmount) {
            // Khóa dòng (Lock for Update) bản ghi User và Coupon để ngăn chặn Race Condition
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            $lockedCoupon = Coupon::where('id', $coupon->id)->lockForUpdate()->first();

            if (!$lockedUser || !$lockedCoupon) {
                throw new Exception('Không thể khóa dữ liệu giao dịch. Vui lòng thử lại.');
            }

            // Kiểm tra lại tính hợp lệ của coupon trong transaction
            if (!$lockedCoupon->isValid(0)) {
                throw new Exception('Mã giảm giá đã hết hiệu lực hoặc lượt dùng trong quá trình xử lý.');
            }

            // Kiểm tra lại xem user đã sử dụng chưa
            $alreadyUsed = $lockedUser->coupons()->where('coupon_id', $lockedCoupon->id)->exists();
            if ($alreadyUsed) {
                throw new Exception('Mã giảm giá đã được tài khoản này sử dụng.');
            }

            $oldBalance = (float) $lockedUser->balance;
            $newBalance = $oldBalance + $redeemAmount;

            // A. Cộng số dư ví tài khoản
            $lockedUser->increment('balance', $redeemAmount);

            // B. Ghi nhận lịch sử giao dịch (Transaction)
            $transactionNo = 'COUPON_' . strtoupper(Str::random(12));
            Transaction::create([
                'user_id' => $lockedUser->id,
                'amount' => (int) $redeemAmount,
                'fee' => 0,
                'net_amount' => (int) $redeemAmount,
                'currency' => 'VND',
                'transaction_no' => $transactionNo,
                'status' => 'SUCCESS',
                'payment_method' => 'COUPON',
                'order_info' => 'Quy đổi mã giảm giá ' . $lockedCoupon->code . ' cộng số dư',
                'pay_date' => now(),
            ]);

            // C. Ghi nhận liên kết sử dụng coupon (coupon_user)
            $lockedUser->coupons()->attach($lockedCoupon->id, ['used_at' => now()]);

            // D. Tăng số lần đã sử dụng của coupon
            $lockedCoupon->increment('used_count');

            // E. Tạo thông báo lưu hệ thống
            Notification::create([
                'user_id' => $lockedUser->id,
                'scope' => 'user',
                'title' => 'Quy đổi mã quà tặng thành công',
                'message' => 'Bạn đã quy đổi thành công mã ' . $lockedCoupon->code . ' nhận ' . number_format($redeemAmount) . 'đ vào tài khoản.',
                'type' => 'success',
                'data' => [
                    'action' => 'update_balance',
                ],
            ]);

            // F. Ghi log hoạt động hệ thống (AuditLog)
            AuditLogService::log(
                'redeem_coupon',
                $lockedCoupon,
                ['balance' => $oldBalance],
                ['balance' => $newBalance, 'redeemed_amount' => $redeemAmount],
                $lockedUser->id
            );
        });

        return $redeemAmount;
    }
}
