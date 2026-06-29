<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test trang quản lý thông báo phía Admin (giao diện tĩnh).
 */
class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_xem_trang_thong_bao_thanh_cong(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($admin)->get(route('admin.notifications.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function user_thuong_khong_truy_cap_duoc(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.notifications.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function khach_vang_lai_bi_redirect(): void
    {
        $response = $this->get(route('admin.notifications.index'));

        $response->assertRedirect();
    }
}
