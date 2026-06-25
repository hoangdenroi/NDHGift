<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TelegramService — Gửi thông báo qua Telegram Bot API.
 *
 * Dùng Http::post() gọi thẳng API, không cần package bên ngoài.
 * Tất cả lỗi đều được bắt + log, không throw → tránh ảnh hưởng luồng chính.
 */
class TelegramService
{
    private string $botToken;

    private string $chatId;

    private bool $enabled;

    private const API_BASE = 'https://api.telegram.org/bot';

    public function __construct()
    {
        $this->botToken = (string) config('services.telegram.bot_token', '');
        $this->chatId = (string) config('services.telegram.chat_id', '');
        $this->enabled = (bool) config('services.telegram.enabled', false);
    }

    /**
     * Gửi tin nhắn text thuần qua Telegram (hỗ trợ HTML parse mode).
     *
     * @return bool Trả true nếu gửi thành công, false nếu lỗi hoặc disabled.
     */
    public function sendMessage(string $text, ?string $chatId = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post(
                self::API_BASE . $this->botToken . '/sendMessage',
                [
                    'chat_id' => $chatId ?? $this->chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ],
            );

            if (!$response->successful()) {
                Log::warning('Telegram sendMessage thất bại', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage exception', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Thông báo nạp tiền thành công.
     */
    public function sendTopupSuccessNotification(User $user, Transaction $transaction): bool
    {
        $amount = number_format((float) $transaction->amount) . 'đ';
        $paymentCode = $transaction->payment_code ?? 'N/A';
        $orderInfo = $transaction->order_info ?? 'N/A';
        $bank = $transaction->bank_code ?? 'N/A';
        $newBalance = number_format((float) ($user->balance ?? 0)) . 'đ';
        
        $text = "💰 <b>NẠP TIỀN THÀNH CÔNG</b>\n\n"
            . "👤 <b>User:</b> {$user->name} ({$user->email})\n"
            . "💵 <b>Số tiền nạp:</b> <code>{$amount}</code>\n"
            . "💵 <b>Số dư mới:</b> <code>{$newBalance}</code>\n"
            . "📦 <b>Mã giao dịch:</b> <code>{$paymentCode}</code>\n"
            . "🏦 <b>Cổng thanh toán:</b> {$bank}\n"
            . "📝 <b>Nội dung CK:</b> <code>{$orderInfo}</code>\n"
            . "📅 <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s');

        return $this->sendMessage($text);
    }

    /**
     * Kiểm tra Telegram đã được cấu hình đầy đủ chưa.
     */
    public function isConfigured(): bool
    {
        return $this->enabled
            && !empty($this->botToken)
            && !empty($this->chatId);
    }
}
