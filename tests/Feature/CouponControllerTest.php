<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'username' => 'couponuser',
            'fullname' => 'Coupon User',
            'email' => 'couponuser@ndhgift.com',
            'balance' => 10000,
        ]);
    }

    /**
     * Test case: Áp dụng mã giảm giá phần trăm thành công.
     */
    public function test_apply_coupon_percent_success(): void
    {
        $coupon = Coupon::create([
            'code' => 'SALE10',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
            'status' => 'public',
            'min_order' => 100000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/vi/apps/api/apply-coupon', [
                'code' => 'SALE10',
                'subtotal' => 200000,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'discount' => 20000, // 10% của 200k là 20k
                'coupon_code' => 'SALE10',
                'type' => 'percent',
                'value' => 10,
            ]);
    }

    /**
     * Test case: Áp dụng mã giảm giá cố định thành công.
     */
    public function test_apply_coupon_fixed_success(): void
    {
        $coupon = Coupon::create([
            'code' => 'GIFT50',
            'type' => 'fixed',
            'value' => 50000,
            'is_active' => true,
            'status' => 'public',
            'min_order' => 100000,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/vi/apps/api/apply-coupon', [
                'code' => 'gift50', // Thử chữ thường, hệ thống tự convert hoa
                'subtotal' => 120000,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'discount' => 50000,
                'coupon_code' => 'GIFT50',
                'type' => 'fixed',
                'value' => 50000,
            ]);
    }

    /**
     * Test case: Áp dụng mã giảm giá thất bại do không tồn tại.
     */
    public function test_apply_coupon_not_found(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/vi/apps/api/apply-coupon', [
                'code' => 'INVALIDCODE',
                'subtotal' => 100000,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại trên hệ thống.',
            ]);
    }

    /**
     * Test case: Áp dụng mã giảm giá thất bại do không đủ min_order.
     */
    public function test_apply_coupon_insufficient_min_order(): void
    {
        Coupon::create([
            'code' => 'MIN500',
            'type' => 'fixed',
            'value' => 50000,
            'is_active' => true,
            'status' => 'public',
            'min_order' => 500000,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/vi/apps/api/apply-coupon', [
                'code' => 'MIN500',
                'subtotal' => 300000,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Đơn hàng tối thiểu 500.000đ để áp dụng mã này.',
            ]);
    }

    /**
     * Test case: Áp dụng mã giảm giá đã hết hạn.
     */
    public function test_apply_coupon_expired(): void
    {
        Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'percent',
            'value' => 15,
            'is_active' => true,
            'status' => 'public',
            'min_order' => 0,
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/vi/apps/api/apply-coupon', [
                'code' => 'EXPIRED',
                'subtotal' => 100000,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Mã giảm giá đã hết hạn sử dụng.',
            ]);
    }

    /**
     * Test case: Áp dụng mã giảm giá đã sử dụng trước đó.
     */
    public function test_apply_coupon_already_used(): void
    {
        $coupon = Coupon::create([
            'code' => 'ONETIME',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
            'status' => 'public',
            'min_order' => 0,
        ]);

        // Liên kết user đã dùng coupon này rồi
        $this->user->coupons()->attach($coupon->id, ['used_at' => now()]);

        $response = $this->actingAs($this->user)
            ->postJson('/vi/apps/api/apply-coupon', [
                'code' => 'ONETIME',
                'subtotal' => 100000,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Bạn đã sử dụng mã giảm giá này rồi. Mỗi tài khoản chỉ được dùng 1 lần.',
            ]);
    }

    /**
     * Test case: Quy đổi mã quà tặng cố định thành số dư ví thành công.
     */
    public function test_redeem_coupon_fixed_success(): void
    {
        $coupon = Coupon::create([
            'code' => 'MONEY100',
            'type' => 'fixed',
            'value' => 100000,
            'is_active' => true,
            'status' => 'public',
            'min_order' => 0,
            'max_uses' => 10,
            'used_count' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/vi/apps/coupon/redeem', [
                'code' => 'MONEY100',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Quy đổi thành công! Đã cộng 100.000đ vào tài khoản.',
                'new_balance' => '110.000đ', // 10k cũ + 100k mới
            ]);

        // Kiểm tra số dư của User tăng lên trong DB
        $this->assertEquals(110000, $this->user->fresh()->balance);

        // Kiểm tra Transaction nạp tiền được tạo
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 100000,
            'status' => 'SUCCESS',
            'payment_method' => 'COUPON',
        ]);

        // Kiểm tra Coupon tăng count dùng và được liên kết
        $this->assertEquals(1, $coupon->fresh()->used_count);
        $this->assertTrue($this->user->coupons()->where('coupon_id', $coupon->id)->exists());

        // Kiểm tra Notification thành công cho user
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'success',
        ]);
    }

    /**
     * Test case: Quy đổi mã quà tặng thất bại do mã là dạng phần trăm.
     */
    public function test_redeem_coupon_percent_fails(): void
    {
        Coupon::create([
            'code' => 'PCT20',
            'type' => 'percent',
            'value' => 20,
            'is_active' => true,
            'status' => 'public',
            'min_order' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/vi/apps/coupon/redeem', [
                'code' => 'PCT20',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Mã giảm giá dạng phần trăm chỉ được áp dụng khi thanh toán đơn hàng.',
            ]);

        // Số dư không thay đổi
        $this->assertEquals(10000, $this->user->fresh()->balance);
    }
}
