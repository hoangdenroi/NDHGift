<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * Kiểm tra auth config đã được thắt chặt đúng.
 */
class AuthConfigTest extends TestCase
{
    public function test_password_reset_token_het_han_sau_15_phut(): void
    {
        $expire = config('auth.passwords.users.expire');

        $this->assertEquals(15, $expire, 'Token reset password phải hết hạn sau 15 phút');
    }

    public function test_password_reset_throttle_la_120_giay(): void
    {
        $throttle = config('auth.passwords.users.throttle');

        $this->assertEquals(120, $throttle, 'Throttle tạo token mới phải là 120 giây');
    }
}
