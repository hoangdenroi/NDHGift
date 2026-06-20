<?php

declare(strict_types=1);

namespace Tests\Feature\App;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử tính năng hiển thị trang Profile cá nhân.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kiểm tra truy cập trang Profile khi chưa đăng nhập.
     * Kỳ vọng: Bị redirect về trang login kèm locale prefix và tham số auth_required.
     */
    public function test_guest_cannot_access_profile_page(): void
    {
        $defaultLocale = config('localization.default_locale', 'vi');

        $response = $this->get("/{$defaultLocale}/apps/profile");

        // Xác nhận redirect về trang đăng nhập với locale và auth_required
        $response->assertRedirect(route('login', [
            'locale' => $defaultLocale,
            'auth_required' => 1,
        ]));
    }

    /**
     * Kiểm tra truy cập trang Profile khi đã đăng nhập thành công.
     * Kỳ vọng: Trả về status 200 và hiển thị đầy đủ thông tin cá nhân của user cùng các stats và menu cứng.
     */
    public function test_authenticated_user_can_access_profile_page_and_see_details(): void
    {
        $defaultLocale = config('localization.default_locale', 'vi');

        // Tạo một user giả lập trong Database sạch
        $user = User::factory()->create([
            'username' => 'testuser',
            'fullname' => 'Nguyen Van A',
            'email' => 'testuser@example.com',
            'avatar_url' => 'https://example.com/avatar.png',
        ]);

        // Đăng nhập và truy cập trang Profile
        $response = $this->actingAs($user)->get("/{$defaultLocale}/apps/profile");

        $response->assertStatus(200);

        // Kiểm tra thông tin cá nhân đọc từ DB
        $response->assertSee('Nguyen Van A');
        $response->assertSee('testuser@example.com');
        $response->assertSee('https://example.com/avatar.png');

        // Kiểm tra các nhãn thông tin trong profile-index.blade.php
        $response->assertSee('Số dư');
        $response->assertSee('Mã tài khoản');
        $response->assertSee('Ngày tạo');
        $response->assertSee('Thông tin cá nhân');
        $response->assertSee('Họ và tên');
        $response->assertSee('Số điện thoại');
        $response->assertSee('Ảnh đại diện');

        // Kiểm tra sự xuất hiện của 3 button mới thêm
        $response->assertSee('Thông báo');
        $response->assertSee('Voucher');
        $response->assertSee('Lịch sử');
    }
}
