<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\User;
use App\Services\CouponService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class CouponServiceTest
 *
 * Unit test kiểm thử toàn diện logic nghiệp vụ trong CouponService.
 */
class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    private CouponService $couponService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->couponService = new CouponService();
    }

    /**
     * Test case: Validate mã giảm giá không tồn tại trên hệ thống.
     */
    public function test_validate_coupon_non_existent(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Mã giảm giá không tồn tại trên hệ thống.');

        $this->couponService->validateCoupon('KHOANG_KHONG_TON_TAI');
    }

    /**
     * Test case: Validate mã giảm giá đã bị khóa (inactive).
     */
    public function test_validate_coupon_inactive(): void
    {
        $coupon = Coupon::create([
            'code' => 'INACTIVE10',
            'type' => 'fixed',
            'value' => 10000,
            'is_active' => false,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Mã giảm giá hiện tại không hoạt động.');

        $this->couponService->validateCoupon($coupon->code);
    }

    /**
     * Test case: Validate mã giảm giá đã hết hạn sử dụng.
     */
    public function test_validate_coupon_expired(): void
    {
        $coupon = Coupon::create([
            'code' => 'EXPIRED10',
            'type' => 'fixed',
            'value' => 10000,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Mã giảm giá đã hết hạn sử dụng.');

        $this->couponService->validateCoupon($coupon->code);
    }

    /**
     * Test case: Validate mã giảm giá chưa đến thời điểm hiệu lực.
     */
    public function test_validate_coupon_not_started_yet(): void
    {
        $coupon = Coupon::create([
            'code' => 'FUTURE10',
            'type' => 'fixed',
            'value' => 10000,
            'is_active' => true,
            'starts_at' => now()->addDay(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Mã giảm giá chưa đến thời gian áp dụng.');

        $this->couponService->validateCoupon($coupon->code);
    }

    /**
     * Test case: Validate mã giảm giá đã đạt số lần sử dụng tối đa.
     */
    public function test_validate_coupon_max_uses_reached(): void
    {
        $coupon = Coupon::create([
            'code' => 'LIMIT10',
            'type' => 'fixed',
            'value' => 10000,
            'is_active' => true,
            'max_uses' => 5,
            'used_count' => 5,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Mã giảm giá đã hết lượt sử dụng trên toàn hệ thống.');

        $this->couponService->validateCoupon($coupon->code);
    }

    /**
     * Test case: Validate mã giảm giá chưa đạt giá trị đơn hàng tối thiểu.
     */
    public function test_validate_coupon_min_order_not_met(): void
    {
        $coupon = Coupon::create([
            'code' => 'MIN100',
            'type' => 'fixed',
            'value' => 10000,
            'is_active' => true,
            'min_order' => 100000,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Đơn hàng tối thiểu 100.000đ để áp dụng mã này.');

        $this->couponService->validateCoupon($coupon->code, 50000);
    }

    /**
     * Test case: Validate mã giảm giá đã được chính user đó dùng rồi.
     */
    public function test_validate_coupon_already_used_by_user(): void
    {
        $user = User::create([
            'username' => 'user1',
            'fullname' => 'Nguyen Van A',
            'email' => 'test1@ndhgift.com',
            'password' => bcrypt('password'),
            'balance' => 0,
        ]);

        $coupon = Coupon::create([
            'code' => 'ONETIME',
            'type' => 'fixed',
            'value' => 10000,
            'is_active' => true,
        ]);

        // Gắn liên kết đã dùng
        $user->coupons()->attach($coupon->id, ['used_at' => now()]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Bạn đã sử dụng mã giảm giá này rồi. Mỗi tài khoản chỉ được dùng 1 lần.');

        $this->couponService->validateCoupon($coupon->code, 0, $user);
    }

    /**
     * Test case: Áp dụng mã phần trăm (percent) thành công.
     */
    public function test_apply_coupon_success_percent(): void
    {
        $user = User::create([
            'username' => 'user2',
            'fullname' => 'Nguyen Van B',
            'email' => 'test2@ndhgift.com',
            'password' => bcrypt('password'),
            'balance' => 0,
        ]);

        $coupon = Coupon::create([
            'code' => 'GIAM10',
            'type' => 'percent',
            'value' => 10, // giảm 10%
            'max_discount' => 50000, // giảm tối đa 50k
            'is_active' => true,
        ]);

        // Đơn hàng 200k -> giảm 20k
        $result = $this->couponService->applyCoupon($coupon->code, 200000, $user);
        $this->assertEquals(20000, $result['discount_amount']);

        // Đơn hàng 1M -> giảm 100k nhưng bị cap tối đa 50k
        $result2 = $this->couponService->applyCoupon($coupon->code, 1000000, $user);
        $this->assertEquals(50000, $result2['discount_amount']);
    }

    /**
     * Test case: Quy đổi thành công mã coupon fixed sang số dư ví.
     */
    public function test_redeem_coupon_success(): void
    {
        $user = User::create([
            'username' => 'user3',
            'fullname' => 'Nguyen Van C',
            'email' => 'test3@ndhgift.com',
            'password' => bcrypt('password'),
            'balance' => 10000, // Số dư ban đầu 10k
        ]);

        $coupon = Coupon::create([
            'code' => 'GIFT50',
            'type' => 'fixed',
            'value' => 50000, // Trị giá 50k
            'is_active' => true,
            'max_uses' => 10,
            'used_count' => 0,
        ]);

        $redeemed = $this->couponService->redeemCoupon($coupon->code, $user);
        
        $this->assertEquals(50000, $redeemed);

        // Kiểm tra số dư người dùng tăng lên thành 60k
        $user = $user->fresh();
        $this->assertEquals(60000, $user->balance);

        // Kiểm tra used_count tăng lên 1
        $coupon = $coupon->fresh();
        $this->assertEquals(1, $coupon->used_count);

        // Kiểm tra lịch sử giao dịch được tạo
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 50000,
            'payment_method' => 'COUPON',
            'status' => 'SUCCESS',
        ]);

        // Kiểm tra liên kết pivot coupon_user được tạo
        $this->assertDatabaseHas('coupon_user', [
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
        ]);

        // Kiểm tra thông báo lưu trong database
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'success',
        ]);
    }

    /**
     * Test case: Cấm quy đổi mã percent sang số dư.
     */
    public function test_redeem_coupon_percent_fails(): void
    {
        $user = User::create([
            'username' => 'user4',
            'fullname' => 'Nguyen Van D',
            'email' => 'test4@ndhgift.com',
            'password' => bcrypt('password'),
            'balance' => 10000,
        ]);

        $coupon = Coupon::create([
            'code' => 'PERCENT20',
            'type' => 'percent',
            'value' => 20,
            'is_active' => true,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Mã giảm giá dạng phần trăm chỉ được áp dụng khi thanh toán đơn hàng.');

        $this->couponService->redeemCoupon($coupon->code, $user);
    }
}
