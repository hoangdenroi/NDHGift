<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kiểm tra khách vãng lai chưa đăng nhập truy cập admin dashboard.
     * Kỳ vọng: Bị chuyển hướng về trang đăng nhập.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login', [
            'locale' => config('localization.default_locale', 'en'),
            'auth_required' => 1,
        ]));
    }

    /**
     * Kiểm tra người dùng có vai trò là user bình thường truy cập admin dashboard.
     * Kỳ vọng: Bị chặn quyền và nhận mã lỗi 403 Forbidden.
     */
    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Kiểm tra người dùng quản trị (admin) truy cập admin dashboard.
     * Kỳ vọng: Truy cập thành công và xem được nội dung dashboard.
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('System Health');
        $response->assertSee('CPU Load');
        $response->assertSee('Memory Usage');
        $response->assertSee('Monthly Performance');
    }
}
