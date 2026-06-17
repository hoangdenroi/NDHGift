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

        $response = $this->actingAs($user)->get('/');

        // User active → truy cập được (200 hoặc redirect bình thường, không bị ép logout)
        $response->assertOk();
    }

    public function test_user_bi_khoa_bi_logout_va_redirect_login(): void
    {
        $user = User::factory()->create([
            'status' => 'suspended',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($user)->get('/');

        // User suspended → bị redirect về login
        $response->assertRedirect(route('login'));
        // Session phải bị hủy → không còn đăng nhập
        $this->assertGuest();
    }

    public function test_user_bi_xoa_mem_bi_logout_va_redirect_login(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_deleted' => true,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_bi_ban_bi_logout(): void
    {
        $user = User::factory()->create([
            'status' => 'banned',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_khong_bi_anh_huong(): void
    {
        // Guest (chưa đăng nhập) → middleware không can thiệp
        $response = $this->get('/');

        $response->assertOk();
    }
}
