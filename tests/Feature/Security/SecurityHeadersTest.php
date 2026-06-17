<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * Kiểm tra SecurityHeaders middleware thiết lập đúng 6 header bảo mật OWASP
 * trên mọi response web.
 */
class SecurityHeadersTest extends TestCase
{
    public function test_response_co_header_x_frame_options(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_response_co_header_x_content_type_options(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_response_co_header_x_xss_protection(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_response_co_header_referrer_policy(): void
    {
        $response = $this->get('/');

        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_response_co_header_permissions_policy(): void
    {
        $response = $this->get('/');

        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_hsts_khong_duoc_set_trong_moi_truong_local(): void
    {
        // Trong môi trường test (local), HSTS không được set
        $response = $this->get('/');

        $response->assertHeaderMissing('Strict-Transport-Security');
    }
}
