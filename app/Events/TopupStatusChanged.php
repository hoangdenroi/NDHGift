<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event TopupStatusChanged
 *
 * Broadcast khi giao dịch nạp tiền thay đổi trạng thái (PENDING → SUCCESS/EXPIRED/CANCELLED).
 * Frontend lắng nghe event này để cập nhật bảng giao dịch chờ realtime.
 */
class TopupStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $transactionId,
        public readonly string $newStatus,
    ) {}

    /**
     * Phát trên kênh riêng tư của user — chỉ user đó nhận được.
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
            'transaction_id' => $this->transactionId,
            'new_status' => $this->newStatus,
        ];
    }
}
