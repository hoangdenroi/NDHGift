<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo user giả lập cho test
        $this->user = User::factory()->create([
            'username' => 'billinguser',
            'fullname' => 'Billing User',
            'email' => 'billinguser@ndhgift.com',
            'balance' => 100000,
        ]);
    }

    /**
     * Test case: Truy cập trang Billing khi chưa đăng nhập.
     * Kỳ vọng: Bị redirect về trang login.
     */
    public function test_billing_index_requires_authentication(): void
    {
        $response = $this->get('/vi/apps/billing');

        $response->assertRedirect('/vi/login?auth_required=1');
    }

    /**
     * Test case: Truy cập trang Billing thành công sau khi đã đăng nhập.
     * Kỳ vọng: Trả về HTTP 200 và load đúng view, truyền đủ biến.
     */
    public function test_billing_index_success(): void
    {
        // 1. Tạo một số mã coupon hoạt động và không hoạt động
        Coupon::create([
            'code' => 'DISCOUNT10',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
            'status' => 'public',
            'min_order' => 0,
        ]);

        Coupon::create([
            'code' => 'EXPIRED20',
            'type' => 'fixed',
            'value' => 20000,
            'is_active' => false, // Không hoạt động
            'status' => 'public',
            'min_order' => 0,
        ]);

        // 2. Tạo một số transaction cho user hiện tại và user khác
        $otherUser = User::factory()->create();

        // Transaction SUCCESS của user hiện tại
        Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 50000,
            'status' => 'SUCCESS',
            'payment_method' => 'SEPAY',
            'transaction_no' => 'TXN001',
            'pay_date' => now(),
        ]);

        // Transaction FAILED của user hiện tại (không được tính vào chartData)
        Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 100000,
            'status' => 'FAILED',
            'payment_method' => 'SEPAY',
            'transaction_no' => 'TXN002',
            'pay_date' => now(),
        ]);

        // Transaction SUCCESS của user khác (không được tính vào chartData của user hiện tại)
        Transaction::create([
            'user_id' => $otherUser->id,
            'amount' => 70000,
            'status' => 'SUCCESS',
            'payment_method' => 'SEPAY',
            'transaction_no' => 'TXN003',
            'pay_date' => now(),
        ]);

        // 3. Thực hiện request
        $response = $this->actingAs($this->user)
            ->get('/vi/apps/billing');

        $response->assertStatus(200);

        // Kiểm tra xem view có chứa dữ liệu mong đợi
        $response->assertViewHas('publicCouponsCount', 1); // Chỉ DISCOUNT10 active và public
        $response->assertViewHas('publicCoupons');
        $response->assertViewHas('chartData');
        $response->assertViewHas('transactions');

        // Kiểm tra xem chartData tính toán đúng tổng doanh thu ngày của user hiện tại (chỉ tính SUCCESS)
        $chartData = $response->viewData('chartData');
        $this->assertEquals(50000, $chartData['day']['total']);
        $this->assertEquals(1, $chartData['day']['count']);
    }
}
