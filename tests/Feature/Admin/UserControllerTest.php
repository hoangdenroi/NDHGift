<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test CRUD và các chức năng quản lý User phía Admin.
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo admin user cho mọi test case
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    // ===== AUTHORIZATION =====

    /** @test */
    public function khong_cho_phep_user_thuong_truy_cap_trang_quan_ly(): void
    {
        $normalUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($normalUser)->get(route('admin.users.index'));

        // Middleware role:admin sẽ chặn — redirect hoặc 403
        $response->assertStatus(403);
    }

    /** @test */
    public function khach_vang_lai_bi_chuyen_huong_ve_trang_dang_nhap(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect();
    }

    // ===== INDEX =====

    /** @test */
    public function admin_xem_danh_sach_nguoi_dung_thanh_cong(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewHas('users');
        $response->assertViewHas('stats');
    }

    /** @test */
    public function loc_nguoi_dung_theo_trang_thai(): void
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'suspended']);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['status' => 'suspended']));

        $response->assertStatus(200);
    }

    /** @test */
    public function tim_kiem_nguoi_dung_theo_email(): void
    {
        User::factory()->create(['email' => 'unique-test@example.com']);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'unique-test']));

        $response->assertStatus(200);
    }

    // ===== STORE =====

    /** @test */
    public function tao_nguoi_dung_moi_thanh_cong(): void
    {
        $userData = [
            'username' => 'newuser123',
            'fullname' => 'Người Dùng Mới',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('toast_type', 'success');
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com', 'username' => 'newuser123']);
    }

    /** @test */
    public function khong_tao_duoc_user_voi_email_da_ton_tai(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'username' => 'anotheruser',
            'fullname' => 'Test',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function validate_mat_khau_toi_thieu_8_ky_tu(): void
    {
        $userData = [
            'username' => 'shortpass',
            'fullname' => 'Test Short Pass',
            'email' => 'shortpass@example.com',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'user',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function chong_xss_trong_ten_nguoi_dung(): void
    {
        $userData = [
            'username' => 'xsstest',
            'fullname' => '<script>alert("xss")</script>Tên Bình Thường',
            'email' => 'xss@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        // Verify script tags bị strip
        $this->assertDatabaseHas('users', ['fullname' => 'alert("xss")Tên Bình Thường']);
    }

    // ===== UPDATE =====

    /** @test */
    public function cap_nhat_nguoi_dung_thanh_cong(): void
    {
        $user = User::factory()->create(['username' => 'old_username', 'fullname' => 'Tên Cũ']);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'username' => 'new_username',
            'fullname' => 'Tên Mới',
            'email' => $user->email,
            'role' => 'user',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'fullname' => 'Tên Mới']);
    }

    // ===== DELETE =====

    /** @test */
    public function xoa_mem_nguoi_dung_thanh_cong(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_deleted' => true]);
    }

    // ===== TOGGLE STATUS =====

    /** @test */
    public function khoa_tai_khoan_co_ly_do(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)->patch(
            route('admin.users.toggle-status', $user),
            ['banned_reason' => 'Vi phạm điều khoản sử dụng']
        );

        $response->assertRedirect(route('admin.users.index'));
        $user->refresh();
        $this->assertEquals('suspended', $user->status);
        $this->assertEquals('Vi phạm điều khoản sử dụng', $user->banned_reason);
        $this->assertNotNull($user->suspended_at);
    }

    /** @test */
    public function mo_khoa_tai_khoan(): void
    {
        $user = User::factory()->create([
            'status' => 'suspended',
            'banned_reason' => 'Lý do cũ',
            'suspended_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.users.toggle-status', $user));

        $response->assertRedirect(route('admin.users.index'));
        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertNull($user->banned_reason);
        $this->assertNull($user->suspended_at);
    }
}
