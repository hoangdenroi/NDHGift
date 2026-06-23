<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event UserTopupSucceeded
 *
 * Phát ra khi người dùng nạp tiền vào tài khoản thành công để xử lý các logic phụ trợ như cộng XP.
 */
class UserTopupSucceeded
{
    use Dispatchable, SerializesModels;

    /**
     * Khởi tạo event.
     */
    public function __construct(
        public User $user,
        public Transaction $transaction
    ) {}
}
