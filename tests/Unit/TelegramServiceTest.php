<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Thiết lập config mặc định cho test
        config([
            'services.telegram.bot_token' => 'test_token',
            'services.telegram.chat_id' => 'test_chat_id',
            'services.telegram.enabled' => true,
        ]);
    }

    /**
     * Test case: Gửi tin nhắn thành công qua Telegram.
     */
    public function test_send_message_success(): void
    {
        Http::fake([
            'https://api.telegram.org/bottest_token/sendMessage' => Http::response(['ok' => true], 200),
        ]);

        $telegramService = new TelegramService();
        $result = $telegramService->sendMessage('Hello World');

        $this->assertTrue($result);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.telegram.org/bottest_token/sendMessage'
                && $request['chat_id'] === 'test_chat_id'
                && $request['text'] === 'Hello World'
                && $request['parse_mode'] === 'HTML';
        });
    }

    /**
     * Test case: Gửi tin nhắn thất bại do API Telegram trả về lỗi.
     */
    public function test_send_message_failed_api_error(): void
    {
        Http::fake([
            'https://api.telegram.org/bottest_token/sendMessage' => Http::response(['ok' => false], 400),
        ]);

        $telegramService = new TelegramService();
        $result = $telegramService->sendMessage('Hello World');

        $this->assertFalse($result);
    }

    /**
     * Test case: Không gửi tin nhắn khi bị disabled.
     */
    public function test_send_message_disabled(): void
    {
        config(['services.telegram.enabled' => false]);

        $telegramService = new TelegramService();
        $result = $telegramService->sendMessage('Hello World');

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    /**
     * Test case: Gửi thông báo nạp tiền thành công.
     */
    public function test_send_topup_success_notification(): void
    {
        Http::fake([
            'https://api.telegram.org/bottest_token/sendMessage' => Http::response(['ok' => true], 200),
        ]);

        $user = User::factory()->make([
            'name' => 'Hoang Den Roi',
            'email' => 'hoang@ndhgift.com',
            'balance' => 150000,
        ]);

        $transaction = new Transaction([
            'amount' => 50000,
            'payment_code' => 'ABCD1234',
            'bank_code' => 'Vietcombank',
            'order_info' => 'SEVQR ABCD1234',
        ]);

        $telegramService = new TelegramService();
        $result = $telegramService->sendTopupSuccessNotification($user, $transaction);

        $this->assertTrue($result);
        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Hoang Den Roi')
                && str_contains($request['text'], '50,000đ')
                && str_contains($request['text'], '150,000đ')
                && str_contains($request['text'], 'ABCD1234')
                && str_contains($request['text'], 'Vietcombank');
        });
    }
}
