<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\BalanceUpdated;
use App\Events\TopupStatusChanged;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class TopupControllerTest
 *
 * Feature test kiểm thử các API endpoint và logic nạp tiền mới (PENDING transactions, cancel, limit, webhook khớp code & fallback).
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
     * Test case: Tạo giao dịch nạp tiền PENDING thành công.
     */
    public function test_create_topup_transaction_success(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/topup/create', [
                'amount' => 50000,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Trích xuất trực tiếp ở root của response JSON
        $this->assertNotNull($response->json('qr_url'));
        $this->assertNotNull($response->json('description'));
        $this->assertNotNull($response->json('transaction.id'));
        $this->assertEquals(50000, $response->json('transaction.amount'));
        $this->assertEquals('PENDING', $response->json('transaction.status'));

        // Kiểm tra xem transaction PENDING được lưu vào database
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 50000,
            'status' => 'PENDING',
        ]);
    }

    /**
     * Test case: Tạo giao dịch thất bại do số tiền không hợp lệ.
     */
    public function test_create_topup_invalid_amount(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/topup/create', [
                'amount' => -1000, // Số tiền âm
            ]);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test case: Tạo giao dịch thất bại khi đạt giới hạn 3 giao dịch PENDING.
     */
    public function test_create_topup_reaches_max_limit(): void
    {
        // Tạo trước 3 giao dịch PENDING
        for ($i = 0; $i < 3; $i++) {
            Transaction::create([
                'user_id' => $this->user->id,
                'amount' => 50000,
                'status' => Transaction::STATUS_PENDING,
                'payment_method' => 'SEPAY',
                'payment_code' => 'CODE' . $i,
                'transaction_no' => 'TXN_TEST_' . $i,
                'expires_at' => now()->addHour(),
            ]);
        }

        // Tạo giao dịch thứ 4
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/topup/create', [
                'amount' => 50000,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Bạn đã có 3 giao dịch đang chờ. Vui lòng hoàn tất hoặc hủy giao dịch cũ trước khi tạo mới.',
            ]);
    }

    /**
     * Test case: Hủy giao dịch PENDING thành công.
     */
    public function test_cancel_pending_transaction_success(): void
    {
        Event::fake([TopupStatusChanged::class]);

        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 50000,
            'status' => Transaction::STATUS_PENDING,
            'payment_method' => 'SEPAY',
            'payment_code' => 'CODE1234',
            'transaction_no' => 'TXN_CANCEL_TEST',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/topup/{$transaction->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Đã hủy giao dịch thành công.',
            ]);

        $this->assertEquals(Transaction::STATUS_CANCELLED, $transaction->fresh()->status);

        // Đảm bảo phát event realtime
        Event::assertDispatched(TopupStatusChanged::class, function ($event) use ($transaction) {
            return $event->transactionId === $transaction->id && $event->newStatus === Transaction::STATUS_CANCELLED;
        });
    }

    /**
     * Test case: Không cho phép hủy giao dịch của user khác (IDOR).
     */
    public function test_cancel_pending_transaction_unauthorized_idor(): void
    {
        $otherUser = User::factory()->create();

        $transaction = Transaction::create([
            'user_id' => $otherUser->id,
            'amount' => 50000,
            'status' => Transaction::STATUS_PENDING,
            'payment_method' => 'SEPAY',
            'payment_code' => 'CODE1234',
            'transaction_no' => 'TXN_CANCEL_TEST_IDOR',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/topup/{$transaction->id}/cancel");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Bạn không có quyền hủy giao dịch này.',
            ]);

        $this->assertEquals(Transaction::STATUS_PENDING, $transaction->fresh()->status);
    }

    /**
     * Test case: Lấy danh sách giao dịch PENDING thành công.
     */
    public function test_get_pending_transactions_list(): void
    {
        Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 50000,
            'status' => Transaction::STATUS_PENDING,
            'payment_method' => 'SEPAY',
            'payment_code' => 'PEND1234',
            'transaction_no' => 'TXN_LIST_1',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/topup/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'transaction_no',
                        'payment_code',
                        'amount',
                        'status',
                        'expires_at',
                    ]
                ]
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test case: Nhận webhook SePay thành công, khớp chính xác payment_code đang chờ.
     */
    public function test_sepay_webhook_match_pending_success(): void
    {
        Event::fake([BalanceUpdated::class, TopupStatusChanged::class]);

        // Giả lập không bắn thông báo thật sang Telegram Bot API
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('sendTopupSuccessNotification')
                ->once()
                ->andReturn(true);
        });

        $apiKey = config('payment.sepay.api_key', 'sepay_api_key_default');

        // Tạo giao dịch PENDING trước
        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 100000,
            'status' => Transaction::STATUS_PENDING,
            'payment_method' => 'SEPAY',
            'payment_code' => 'ABC12345',
            'transaction_no' => 'TXN_MATCH_WEBHOOK',
            'expires_at' => now()->addHour(),
        ]);

        // Giả lập Webhook SePay gửi lên chứa nội dung CK là mã thanh toán duy nhất
        $response = $this->postJson('/api/v1/topup/sepay-hook', [
            'id' => 99999,
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-06-22 10:00:00',
            'accountNumber' => '10003179213',
            'code' => '',
            'content' => "SEVQR ABC12345", // Nội dung khớp payment_code
            'transferType' => 'in',
            'transferAmount' => 100000,
            'accumulated' => 100000,
            'subAccount' => '',
            'referenceCode' => 'FT999999',
            'description' => 'Chuyển tiền qua QR',
        ], [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Giao dịch khớp và xử lý thành công.',
            ]);

        // Kiểm tra số dư tài khoản tăng lên
        $this->assertEquals(100000, $this->user->fresh()->balance);

        // Kiểm tra giao dịch PENDING chuyển thành SUCCESS
        $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->fresh()->status);
        $this->assertEquals('99999', $transaction->fresh()->gateway_transaction_id);

        Event::assertDispatched(BalanceUpdated::class);
        Event::assertDispatched(TopupStatusChanged::class, function ($event) use ($transaction) {
            return $event->transactionId === $transaction->id && $event->newStatus === Transaction::STATUS_SUCCESS;
        });
    }

    /**
     * Test case: Nhận webhook SePay thành công, fallback khớp unitcode trực tiếp.
     */
    public function test_sepay_webhook_fallback_unitcode_success(): void
    {
        Event::fake([BalanceUpdated::class, TopupStatusChanged::class]);

        // Giả lập không bắn thông báo thật sang Telegram Bot API
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('sendTopupSuccessNotification')
                ->once()
                ->andReturn(true);
        });

        $apiKey = config('payment.sepay.api_key', 'sepay_api_key_default');
        $unitcode = $this->user->unitcode;

        // Giả lập Webhook SePay gửi lên với nội dung "SEVQR {unitcode}"
        $response = $this->postJson('/api/v1/topup/sepay-hook', [
            'id' => 88888,
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-06-22 10:00:00',
            'accountNumber' => '10003179213',
            'code' => '',
            'content' => "SEVQR {$unitcode}", // Khớp theo unitcode của user
            'transferType' => 'in',
            'transferAmount' => 150000,
            'accumulated' => 150000,
            'subAccount' => '',
            'referenceCode' => 'FT888888',
            'description' => 'Chuyển tiền qua QR',
        ], [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Giao dịch thành công (fallback unitcode).',
            ]);

        $this->assertEquals(150000, $this->user->fresh()->balance);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 150000,
            'status' => 'SUCCESS',
            'gateway_transaction_id' => '88888',
        ]);
    }

    /**
     * Test case: Webhook gửi trùng lặp cho giao dịch đã xử lý.
     */
    public function test_sepay_webhook_already_processed(): void
    {
        $apiKey = config('payment.sepay.api_key', 'sepay_api_key_default');

        Transaction::create([
            'transaction_no' => 'TXN12345678',
            'gateway_transaction_id' => '12345',
            'user_id' => $this->user->id,
            'amount' => 100000,
            'payment_method' => 'SEPAY',
            'status' => 'SUCCESS',
        ]);

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
                'message' => 'Giao dịch đã được xử lý trước đó.',
            ]);

        $this->assertEquals(0, $this->user->fresh()->balance);
    }

    /**
     * Test case: Webhook SePay gửi mã không tồn tại trong hệ thống.
     */
    public function test_sepay_webhook_not_found(): void
    {
        $apiKey = config('payment.sepay.api_key', 'sepay_api_key_default');

        $response = $this->postJson('/api/v1/topup/sepay-hook', [
            'id' => 77777,
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-06-22 10:00:00',
            'accountNumber' => '10003179213',
            'content' => 'SEVQR NOTEXIST',
            'transferType' => 'in',
            'transferAmount' => 100000,
            'accumulated' => 100000,
        ], [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Không tìm thấy giao dịch chờ hoặc user với mã: NOTEXIST',
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => null,
            'amount' => 100000,
            'status' => 'FAILED',
            'gateway_transaction_id' => '77777',
            'failure_reason' => 'Không tìm thấy giao dịch chờ hoặc user với mã: NOTEXIST',
        ]);
    }

    /**
     * Test case: Artisan command topup:expire-pending hoạt động chính xác.
     */
    public function test_artisan_command_expire_pending_transactions(): void
    {
        Event::fake([TopupStatusChanged::class]);

        // Tạo 1 giao dịch PENDING đã quá hạn
        $expiredTx = Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 50000,
            'status' => Transaction::STATUS_PENDING,
            'payment_method' => 'SEPAY',
            'payment_code' => 'EXP12345',
            'transaction_no' => 'TXN_EXP_1',
            'expires_at' => now()->subMinutes(10), // Đã hết hạn cách đây 10 phút
        ]);

        // Tạo 1 giao dịch PENDING chưa quá hạn
        $activeTx = Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 50000,
            'status' => Transaction::STATUS_PENDING,
            'payment_method' => 'SEPAY',
            'payment_code' => 'ACT12345',
            'transaction_no' => 'TXN_ACT_1',
            'expires_at' => now()->addMinutes(30), // Chưa quá hạn
        ]);

        // Chạy Artisan command
        Artisan::call('topup:expire-pending');

        $this->assertEquals(Transaction::STATUS_EXPIRED, $expiredTx->fresh()->status);
        $this->assertEquals(Transaction::STATUS_PENDING, $activeTx->fresh()->status);

        Event::assertDispatched(TopupStatusChanged::class, function ($event) use ($expiredTx) {
            return $event->transactionId === $expiredTx->id && $event->newStatus === Transaction::STATUS_EXPIRED;
        });
    }

    /**
     * Test case: Giao dịch nạp tiền quá hạn tự động chuyển sang EXPIRED khi gọi API get pending.
     */
    public function test_auto_expire_pending_transactions_on_fetch_success(): void
    {
        Event::fake([TopupStatusChanged::class]);

        // Tạo 1 giao dịch PENDING đã quá hạn
        $expiredTx = Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 50000,
            'status' => Transaction::STATUS_PENDING,
            'payment_method' => 'SEPAY',
            'payment_code' => 'EXP67890',
            'transaction_no' => 'TXN_EXP_AUTO_1',
            'expires_at' => now()->subMinutes(5), // Đã hết hạn cách đây 5 phút
        ]);

        // Tạo 1 giao dịch PENDING chưa quá hạn
        $activeTx = Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 50000,
            'status' => Transaction::STATUS_PENDING,
            'payment_method' => 'SEPAY',
            'payment_code' => 'ACT67890',
            'transaction_no' => 'TXN_ACT_AUTO_1',
            'expires_at' => now()->addMinutes(30), // Chưa quá hạn
        ]);

        // Gọi API pending
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/topup/pending');

        $response->assertStatus(200);

        // Kiểm tra trong DB: expiredTx đã chuyển sang EXPIRED, activeTx vẫn PENDING
        $this->assertEquals(Transaction::STATUS_EXPIRED, $expiredTx->fresh()->status);
        $this->assertEquals(Transaction::STATUS_PENDING, $activeTx->fresh()->status);

        // API chỉ trả về giao dịch chưa hết hạn
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('ACT67890', $response->json('data.0.payment_code'));

        // Đảm bảo event status change được phát cho giao dịch hết hạn
        Event::assertDispatched(TopupStatusChanged::class, function ($event) use ($expiredTx) {
            return $event->transactionId === $expiredTx->id && $event->newStatus === Transaction::STATUS_EXPIRED;
        });
    }
}

