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
    public function test_guest_can_access_profile_page_and_see_auth_buttons(): void
    {
        $defaultLocale = config('localization.default_locale', 'vi');

        $response = $this->get("/{$defaultLocale}/apps/profile");

        $response->assertStatus(200);
        $response->assertSee('Chào mừng khách');
        $response->assertSee('Đăng nhập');
        $response->assertSee('Đăng ký');
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

        // Kiểm tra sự xuất hiện của 3 button mới thêm dưới Avatar
        $response->assertSee('Đổi voucher');
        $response->assertSee('Lịch sử');
        $response->assertSee('Đăng xuất');

        // Kiểm tra sự xuất hiện của các menu chức năng mới ở cột phải
        $response->assertSee('Chỉnh sửa thông tin');
        $response->assertSee('Hóa đơn');
        $response->assertSee('Cài đặt');
        $response->assertSee('Trợ giúp');
    }

    /**
     * Kiểm tra cập nhật thông tin cá nhân thành công.
     */
    public function test_authenticated_user_can_update_profile_details(): void
    {
        $defaultLocale = config('localization.default_locale', 'vi');
        $user = User::factory()->create([
            'fullname' => 'Nguyen Van A',
            'phone' => '0987654321',
        ]);

        $response = $this->actingAs($user)->post("/{$defaultLocale}/apps/profile", [
            'fullname' => 'Nguyen Van B',
            'phone' => '0123456789',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Cập nhật thông tin cá nhân thành công!');

        $user->refresh();
        $this->assertEquals('Nguyen Van B', $user->fullname);
        $this->assertEquals('0123456789', $user->phone);
    }

    /**
     * Kiểm tra validation khi cập nhật với dữ liệu không hợp lệ.
     */
    public function test_user_cannot_update_profile_with_invalid_data(): void
    {
        $defaultLocale = config('localization.default_locale', 'vi');
        $user = User::factory()->create([
            'fullname' => 'Nguyen Van A',
        ]);

        $response = $this->actingAs($user)->post("/{$defaultLocale}/apps/profile", [
            'fullname' => '', // fullname không được để trống
        ]);

        $response->assertSessionHasErrors(['fullname']);
    }

    /**
     * Kiểm tra Rate Limiting giới hạn 5 lần/phút khi cập nhật profile.
     */
    public function test_profile_update_rate_limiting(): void
    {
        $defaultLocale = config('localization.default_locale', 'vi');
        $user = User::factory()->create();

        // Gửi 5 request đầu tiên thành công
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($user)->post("/{$defaultLocale}/apps/profile", [
                'fullname' => 'Test Name ' . $i,
            ]);
            $response->assertStatus(302);
        }

        // Request thứ 6 bị chặn bởi rate limit
        $response = $this->actingAs($user)->post("/{$defaultLocale}/apps/profile", [
            'fullname' => 'Blocked Name',
        ]);

        $response->assertSessionHasErrors(['rate_limit']);
    }

    /**
     * Kiểm tra cập nhật email đăng nhập thành công.
     */
    public function test_authenticated_user_can_update_email_successfully(): void
    {
        $defaultLocale = config('localization.default_locale', 'vi');
        $user = User::factory()->create([
            'email' => 'oldemail@example.com',
        ]);

        $response = $this->actingAs($user)->post("/{$defaultLocale}/apps/profile", [
            'email' => 'newemail@example.com',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Cập nhật email đăng nhập thành công!');

        $user->refresh();
        $this->assertEquals('newemail@example.com', $user->email);
    }

    /**
     * Kiểm tra không thể cập nhật email nếu trùng với email của người khác.
     */
    public function test_user_cannot_update_email_with_existing_email(): void
    {
        $defaultLocale = config('localization.default_locale', 'vi');
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
        ]);
        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
        ]);

        $response = $this->actingAs($user1)->post("/{$defaultLocale}/apps/profile", [
            'email' => 'user2@example.com', // Trùng email của user2
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Kiểm tra cập nhật cài đặt thành công cho user đã đăng nhập.
     */
    public function test_authenticated_user_can_update_settings_successfully(): void
    {
        $user = User::factory()->create();

        // 1. Test update theme
        $response = $this->actingAs($user)->postJson('/api/v1/settings', [
            'key' => 'theme',
            'value' => [
                'mode' => 'dark',
                'primaryColor' => '#ff0000',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $user->refresh();
        $this->assertEquals('dark', $user->settings['theme']['mode']);
        $this->assertEquals('#ff0000', $user->settings['theme']['primaryColor']);

        // 2. Test update notifications
        $response = $this->actingAs($user)->postJson('/api/v1/settings', [
            'key' => 'notifications',
            'value' => [
                'email' => false,
                'push' => true,
            ],
        ]);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertFalse($user->settings['notifications']['email']);
        $this->assertTrue($user->settings['notifications']['push']);

        // 3. Test update language
        $response = $this->actingAs($user)->postJson('/api/v1/settings', [
            'key' => 'language',
            'value' => 'vi',
        ]);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertEquals('vi', $user->settings['language']);
    }

    /**
     * Kiểm tra khách vãng lai (chưa đăng nhập) không thể gọi API cập nhật cài đặt.
     */
    public function test_guest_cannot_update_settings(): void
    {
        $response = $this->postJson('/api/v1/settings', [
            'key' => 'language',
            'value' => 'vi',
        ]);

        // Route auth middleware mặc định trả về 401 Unauthorized khi gửi request JSON
        $response->assertStatus(401);
    }

    /**
     * Kiểm tra validation chặn các cài đặt không hợp lệ.
     */
    public function test_user_cannot_update_settings_with_invalid_keys_or_values(): void
    {
        $user = User::factory()->create();

        // 1. Key không hợp lệ
        $response = $this->actingAs($user)->postJson('/api/v1/settings', [
            'key' => 'invalid_key',
            'value' => 'value',
        ]);
        $response->assertStatus(422);

        // 2. Value theme sai format màu sắc
        $response = $this->actingAs($user)->postJson('/api/v1/settings', [
            'key' => 'theme',
            'value' => [
                'mode' => 'dark',
                'primaryColor' => 'not-a-color', // không khớp regex #hex
            ],
        ]);
        $response->assertStatus(422);

        // 3. Value notifications sai format kiểu dữ liệu
        $response = $this->actingAs($user)->postJson('/api/v1/settings', [
            'key' => 'notifications',
            'value' => 'not-an-array',
        ]);
        $response->assertStatus(422);

        // 4. Value language không được hỗ trợ
        $response = $this->actingAs($user)->postJson('/api/v1/settings', [
            'key' => 'language',
            'value' => 'fr', // tiếng Pháp không được định nghĩa trong in:vi,en
        ]);
        $response->assertStatus(422);
    }
}


