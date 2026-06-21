<?php

declare(strict_types=1);

namespace Tests\Feature\App;

use Tests\TestCase;

/**
 * Kiểm thử tính năng hiển thị trang Giới thiệu (About Page) của ứng dụng.
 */
class AboutTest extends TestCase
{
    /**
     * Kiểm tra truy cập trang Giới thiệu bằng tiếng Việt.
     * Kỳ vọng: Trả về HTTP 200, tiêu đề và breadcrumbs được dịch thành tiếng Việt.
     */
    public function test_guest_can_access_about_page_in_vietnamese(): void
    {
        $response = $this->get('/vi/apps/about');

        $response->assertStatus(200);

        // Kiểm tra tiêu đề chính và breadcrumbs bằng tiếng Việt (được dịch từ vi.json)
        $response->assertSee('Về NDHGift');
        $response->assertSee('NDHGift');

        // Kiểm tra các phần nội dung tĩnh của trang giới thiệu
        $response->assertSee('Chúng tôi là ai?');
        $response->assertSee('Sứ mệnh');
        $response->assertSee('Tầm nhìn');
        $response->assertSee('Con số ấn tượng');
        $response->assertSee('Khám phá thế giới quà tặng');
    }

    /**
     * Kiểm tra truy cập trang Giới thiệu bằng tiếng Anh.
     * Kỳ vọng: Trả về HTTP 200, các tiêu đề hiển thị bằng tiếng Anh.
     */
    public function test_guest_can_access_about_page_in_english(): void
    {
        $response = $this->get('/en/apps/about');

        $response->assertStatus(200);

        // Kiểm tra tiêu đề hiển thị bằng tiếng Anh (được dịch từ en.json)
        $response->assertSee('About');
        $response->assertSee('NDHGift');
    }
}
