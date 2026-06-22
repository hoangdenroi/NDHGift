<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event BalanceUpdated
 *
 * Phát tín hiệu khi số dư tài khoản người dùng thay đổi (ví dụ nạp tiền thành công).
 * Hỗ trợ đồng bộ hóa giao diện thông qua WebSocket realtime.
 */
class BalanceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly float $newBalance,
        public readonly float $amount,
        public readonly string $message,
    ) {}

    /**
     * Chỉ phát trên kênh riêng tư của người dùng tương ứng.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    /**
     * Dữ liệu gửi kèm payload broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'new_balance' => $this->newBalance,
            'amount' => $this->amount,
            'message' => $this->message,
        ];
    }
}
