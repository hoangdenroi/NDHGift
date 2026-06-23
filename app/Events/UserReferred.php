<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event UserReferred
 *
 * Phát ra khi có người dùng mới đăng ký thành công qua mã giới thiệu (affiliate) của người khác.
 */
class UserReferred
{
    use Dispatchable, SerializesModels;

    /**
     * Khởi tạo event.
     */
    public function __construct(
        public User $referrer,
        public User $referee
    ) {}
}
