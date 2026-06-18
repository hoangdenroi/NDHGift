<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm tra EnsureUserIsActive middleware:
 * - User active → truy cập bình thường
 * - User bị khóa (suspended) → bị logout + redirect login
 * - User bị xóa mềm (is_deleted) → bị logout + redirect login
 */
class EnsureUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_active_truy_cap_binh_thuong(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_deleted' => false,
        ]);

        // Sử dụng route có locale prefix thay vì root URL '/'
        $response = $this->actingAs($user)->get('/en');

        // User active → truy cập được (200)
        $response->assertOk();
    }

    public function test_user_bi_khoa_bi_logout_va_redirect_login(): void
    {
        $user = User::factory()->create([
            'status' => 'suspended',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($user)->get('/en');

        // User suspended → bị redirect về login với locale tương ứng
        $response->assertRedirect(route('login', ['locale' => 'en']));
        // Session phải bị hủy → không còn đăng nhập
        $this->assertGuest();
    }

    public function test_user_bi_xoa_mem_bi_logout_va_redirect_login(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_deleted' => true,
        ]);

        $response = $this->actingAs($user)->get('/en');

        $response->assertRedirect(route('login', ['locale' => 'en']));
        $this->assertGuest();
    }

    public function test_user_bi_ban_bi_logout(): void
    {
        $user = User::factory()->create([
            'status' => 'banned',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($user)->get('/en');

        $response->assertRedirect(route('login', ['locale' => 'en']));
        $this->assertGuest();
    }

    public function test_guest_khong_bi_anh_huong(): void
    {
        // Guest (chưa đăng nhập) truy cập trang chủ /en thì trả về 200
        $response = $this->get('/en');

        $response->assertOk();
    }
}
