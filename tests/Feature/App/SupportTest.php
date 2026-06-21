<?php

declare(strict_types=1);

namespace Tests\Feature\App;

use Tests\TestCase;

/**
 * Kiểm thử tính năng hiển thị trang Hỗ trợ (Support Page) của ứng dụng.
 */
class SupportTest extends TestCase
{
    /**
     * Kiểm tra truy cập trang Hỗ trợ bằng tiếng Việt.
     * Kỳ vọng: Trả về HTTP 200, tiêu đề và các nhãn hiển thị bằng tiếng Việt.
     */
    public function test_guest_can_access_support_page_in_vietnamese(): void
    {
        $response = $this->get('/vi/apps/support');

        $response->assertStatus(200);

        // Kiểm tra tiêu đề chính tiếng Việt
        $response->assertSee('Hỗ trợ');
        $response->assertSee('NDHGift');

        // Kiểm tra các phần nội dung tĩnh tiếng Việt
        $response->assertSee('Gửi tin nhắn');
        $response->assertSee('Thông tin liên hệ');
        $response->assertSee('Theo dõi chúng tôi');
        $response->assertSee('Hà Nội, Việt Nam');
    }

    /**
     * Kiểm tra truy cập trang Hỗ trợ bằng tiếng Anh.
     * Kỳ vọng: Trả về HTTP 200, các tiêu đề hiển thị bằng tiếng Anh.
     */
    public function test_guest_can_access_support_page_in_english(): void
    {
        $response = $this->get('/en/apps/support');

        $response->assertStatus(200);

        // Kiểm tra tiêu đề chính tiếng Anh
        $response->assertSee('Support');
        $response->assertSee('NDHGift');

        // Kiểm tra các phần nội dung tĩnh tiếng Anh
        $response->assertSee('Send Message');
        $response->assertSee('Contact Information');
        $response->assertSee('Follow Us');
        $response->assertSee('Ha Noi, Viet Nam');
    }
}
