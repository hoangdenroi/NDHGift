<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\BalanceUpdated;
use Tests\TestCase;

/**
 * Class TopupControllerTest
 *
 * Feature test kiểm thử các API endpoint của TopupController (QR Code, SePay webhook).
 */
class TopupControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo user thử nghiệm bằng factory để đảm bảo đầy đủ thuộc tính mặc định
        $this->user = User::factory()->create([
            'username' => 'topupuser',
            'fullname' => 'Topup User',
            'email' => 'topupuser@ndhgift.com',
            'balance' => 0,
        ]);
    }

    /**
     * Test case: Lấy QR code nạp tiền thành công.
     */
    public function test_get_payment_qr_success(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/topup/qrcode', [
                'amount' => 50000,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Trích xuất trực tiếp ở root của response JSON
        $this->assertNotNull($response->json('qr_url'));
        $this->assertNotNull($response->json('description'));
    }

    /**
     * Test case: Lấy QR code thất bại do số tiền không hợp lệ.
     */
    public function test_get_payment_qr_invalid_amount(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/topup/qrcode', [
                'amount' => -1000, // Số tiền âm
            ]);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test case: Nhận webhook SePay thành công, xử lý nạp tiền tự động.
     */
    public function test_sepay_webhook_success(): void
    {
        Event::fake([BalanceUpdated::class]);

        $apiKey = config('payment.sepay.api_key', 'sepay_api_key_default');
        $unitcode = $this->user->unitcode;

        // Giả lập Webhook SePay gửi lên với đầy đủ các trường bắt buộc
        $response = $this->postJson('/api/v1/topup/sepay-hook', [
            'id' => 12345, // ID giao dịch bên SePay
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-06-22 10:00:00',
            'accountNumber' => '10003179213',
            'code' => '',
            'content' => "SEVQR {$unitcode}", // Khớp cú pháp nạp tiền định danh
            'transferType' => 'in',
            'transferAmount' => 100000, // Số tiền nạp
            'accumulated' => 100000,
            'subAccount' => '',
            'referenceCode' => 'FT12345678',
            'description' => 'Chuyển tiền qua QR',
        ], [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Giao dịch thành công',
            ]);

        // Kiểm tra số dư tài khoản tăng lên
        $this->assertEquals(100000, $this->user->fresh()->balance);

        // Kiểm tra giao dịch được ghi nhận thành công trong DB
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 100000,
            'status' => 'SUCCESS',
            'gateway_transaction_id' => '12345',
        ]);

        // Kiểm tra Event được trigger
        Event::assertDispatched(BalanceUpdated::class);

        // Kiểm tra thông báo được tạo
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'success',
        ]);
    }

    /**
     * Test case: Nhận webhook SePay thành công khi nội dung chuyển khoản viết liền không có khoảng trắng.
     */
    public function test_sepay_webhook_success_no_spaces(): void
    {
        Event::fake([BalanceUpdated::class]);

        $apiKey = config('payment.sepay.api_key', 'sepay_api_key_default');
        $unitcode = $this->user->unitcode;

        // Giả lập Webhook SePay gửi lên với nội dung viết liền "SEVQR01KVP..."
        $response = $this->postJson('/api/v1/topup/sepay-hook', [
            'id' => 12349, // ID giao dịch khác
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-06-22 10:00:00',
            'accountNumber' => '10003179213',
            'code' => '',
            'content' => "SEVQR{$unitcode}", // Viết liền không có khoảng trắng
            'transferType' => 'in',
            'transferAmount' => 150000,
            'accumulated' => 150000,
            'subAccount' => '',
            'referenceCode' => 'FT12345679',
            'description' => 'Chuyển tiền qua QR viet lien',
        ], [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Giao dịch thành công',
            ]);

        // Kiểm tra số dư tài khoản tăng lên
        $this->assertEquals(150000, $this->user->fresh()->balance);

        // Kiểm tra giao dịch được ghi nhận
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 150000,
            'status' => 'SUCCESS',
            'gateway_transaction_id' => '12349',
        ]);
    }

    /**
     * Test case: Webhook SePay bị từ chối do sai Authorization API key.
     */
    public function test_sepay_webhook_invalid_authorization(): void
    {
        $response = $this->postJson('/api/v1/topup/sepay-hook', [
            'id' => 12345,
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-06-22 10:00:00',
            'accountNumber' => '10003179213',
            'content' => 'SEVQR 123',
            'transferType' => 'in',
            'transferAmount' => 100000,
            'accumulated' => 100000,
        ], [
            'Authorization' => 'Apikey WRONG_KEY',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid API Key.',
            ]);
    }

    /**
     * Test case: Webhook SePay gửi mã unitcode không tồn tại.
     * Kỳ vọng: Giao dịch thất bại (FAILED) được ghi lại, vẫn trả về 200 cho SePay.
     */
    public function test_sepay_webhook_user_not_found(): void
    {
        $apiKey = config('payment.sepay.api_key', 'sepay_api_key_default');

        $response = $this->postJson('/api/v1/topup/sepay-hook', [
            'id' => 12346,
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-06-22 10:00:00',
            'accountNumber' => '10003179213',
            'content' => 'SEVQR NONEXISTENT', // Unitcode không tồn tại
            'transferType' => 'in',
            'transferAmount' => 100000,
            'accumulated' => 100000,
        ], [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Kiểm tra xem giao dịch FAILED được ghi nhận lại trong DB
        $this->assertDatabaseHas('transactions', [
            'user_id' => null, // Không gắn được vào user nào
            'amount' => 100000,
            'status' => 'FAILED',
            'gateway_transaction_id' => '12346',
            'failure_reason' => 'Không tìm thấy user với mã unitcode: NONEXISTENT',
        ]);
    }

    /**
     * Test case: Webhook gửi trùng lặp cho giao dịch đã xử lý (SUCCESS).
     * Kỳ vọng: Trả về success true ngay lập tức để SePay không retry.
     */
    public function test_sepay_webhook_already_processed(): void
    {
        $apiKey = config('payment.sepay.api_key', 'sepay_api_key_default');

        // Giao dịch đã được lưu thành công trước đó
        Transaction::create([
            'transaction_no' => 'TXN12345678',
            'gateway_transaction_id' => '12345', // Đã xử lý ID này
            'user_id' => $this->user->id,
            'amount' => 100000,
            'payment_method' => 'SEPAY',
            'status' => 'SUCCESS',
        ]);

        // Giả lập webhook gửi trùng lặp
        $response = $this->postJson('/api/v1/topup/sepay-hook', [
            'id' => 12345, // ID trùng lặp
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-06-22 10:00:00',
            'accountNumber' => '10003179213',
            'content' => "SEVQR {$this->user->unitcode}",
            'transferType' => 'in',
            'transferAmount' => 100000,
            'accumulated' => 100000,
        ], [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Giao dịch đã được thực hiện trước đó.',
            ]);

        // Đảm bảo số dư không bị cộng dồn lần 2
        $this->assertEquals(0, $this->user->fresh()->balance);
    }
}
