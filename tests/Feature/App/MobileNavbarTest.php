<?php

declare(strict_types=1);

namespace Tests\Feature\App;

use Tests\TestCase;

/**
 * Kiểm thử tính năng hiển thị Mobile Navbar trên ứng dụng.
 */
class MobileNavbarTest extends TestCase
{
    /**
     * Kiểm tra xem Mobile Navbar có hiển thị chính xác các tab và icon tương ứng
     * khi người dùng truy cập trang chủ hay không.
     */
    public function test_mobile_navbar_is_rendered_with_correct_items(): void
    {
        $defaultLocale = config('localization.default_locale', 'vi');
        
        // Truy cập trang chủ với locale mặc định
        $response = $this->get("/{$defaultLocale}");

        // Xác nhận HTTP Status là 200
        $response->assertStatus(200);

        // Kiểm tra sự hiện diện của các tab trên Mobile Navbar
        $response->assertSee('Trang chủ');
        $response->assertSee('Quà tặng');
        $response->assertSee('Hỗ trợ');
        $response->assertSee('Hồ sơ');
        
        // Kiểm tra xem icon dấu cộng ở giữa có tồn tại hay không
        $response->assertSee('add');
        
        // Kiểm tra các class CSS đặc trưng của Mobile Navbar (ẩn trên desktop, hiển thị trên mobile/tablet)
        $response->assertSee('fixed bottom-0 left-0 right-0');
        $response->assertSee('lg:hidden');
    }
}
