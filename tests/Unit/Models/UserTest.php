<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test Model User — kiểm tra cấu trúc bảng, scopes, helpers và bảo mật Mass Assignment.
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    // ===== CẤU TRÚC BẢNG =====

    #[Test]
    public function tao_user_voi_du_lieu_hop_le(): void
    {
        $user = User::create([
            'username' => 'nguyenvana',
            'fullname' => 'Nguyễn Văn A',
            'email' => 'test@example.com',
            'password' => 'SecureP@ss123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'username' => 'nguyenvana',
            'role' => 'user',
            'status' => 'active',
            'is_deleted' => false,
        ]);

        // Unitcode tự động sinh ULID 26 ký tự
        $this->assertNotNull($user->unitcode);
        $this->assertSame(26, strlen($user->unitcode));

        // Balance mặc định = 0
        $this->assertEquals(0, (float) $user->fresh()->balance);
    }

    #[Test]
    public function unitcode_tu_dong_sinh_ulid_duy_nhat(): void
    {
        $user1 = User::create([
            'username' => 'user1',
            'fullname' => 'User 1',
            'email' => 'user1@example.com',
            'password' => 'password',
        ]);

        $user2 = User::create([
            'username' => 'user2',
            'fullname' => 'User 2',
            'email' => 'user2@example.com',
            'password' => 'password',
        ]);

        $this->assertNotEquals($user1->unitcode, $user2->unitcode);
    }

    #[Test]
    public function email_phai_la_duy_nhat(): void
    {
        User::create([
            'username' => 'user1',
            'fullname' => 'User 1',
            'email' => 'duplicate@example.com',
            'password' => 'password',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'username' => 'user2',
            'fullname' => 'User 2',
            'email' => 'duplicate@example.com',
            'password' => 'password',
        ]);
    }

    #[Test]
    public function username_phai_la_duy_nhat(): void
    {
        User::create([
            'username' => 'duplicateuser',
            'fullname' => 'User 1',
            'email' => 'user1@example.com',
            'password' => 'password',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'username' => 'duplicateuser',
            'fullname' => 'User 2',
            'email' => 'user2@example.com',
            'password' => 'password',
        ]);
    }

    // ===== BẢO MẬT MASS ASSIGNMENT =====

    #[Test]
    public function role_khong_the_gan_hang_loat(): void
    {
        $user = User::create([
            'username' => 'hacker',
            'fullname' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password',
            'role' => 'admin', // Cố tình truyền role
        ]);

        // Role phải giữ nguyên giá trị mặc định trong Database, không bị ghi đè
        $this->assertSame('user', $user->fresh()->role);
    }

    // ===== SCOPES =====

    #[Test]
    public function scope_active_chi_lay_user_hoat_dong(): void
    {
        // User hoạt động
        User::create([
            'username' => 'active',
            'fullname' => 'Active User',
            'email' => 'active@example.com',
            'password' => 'password',
        ]);

        // User bị khóa
        $suspended = User::create([
            'username' => 'suspended',
            'fullname' => 'Suspended User',
            'email' => 'suspended@example.com',
            'password' => 'password',
        ]);
        $suspended->update(['status' => 'suspended']);

        // User đã xóa mềm
        $deleted = User::create([
            'username' => 'deleted',
            'fullname' => 'Deleted User',
            'email' => 'deleted@example.com',
            'password' => 'password',
        ]);
        $deleted->softDelete();

        $activeUsers = User::active()->get();

        $this->assertCount(1, $activeUsers);
        $this->assertSame('active@example.com', $activeUsers->first()->email);
    }

    #[Test]
    public function scope_suspended_chi_lay_user_bi_khoa(): void
    {
        User::create([
            'username' => 'active',
            'fullname' => 'Active',
            'email' => 'active@example.com',
            'password' => 'password',
        ]);

        $suspended = User::create([
            'username' => 'suspended',
            'fullname' => 'Suspended',
            'email' => 'suspended@example.com',
            'password' => 'password',
        ]);
        $suspended->update(['status' => 'suspended']);

        $suspendedUsers = User::suspended()->get();

        $this->assertCount(1, $suspendedUsers);
        $this->assertSame('suspended@example.com', $suspendedUsers->first()->email);
    }

    // ===== HELPER METHODS =====

    #[Test]
    public function soft_delete_danh_dau_va_khoi_phuc(): void
    {
        $user = User::create([
            'username' => 'testuser',
            'fullname' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Xóa mềm
        $result = $user->softDelete();
        $this->assertTrue($result);
        $this->assertTrue($user->fresh()->is_deleted);
        $this->assertNotNull($user->fresh()->deleted_at);

        // Khôi phục
        $result = $user->restoreAccount();
        $this->assertTrue($result);
        $this->assertFalse($user->fresh()->is_deleted);
        $this->assertNull($user->fresh()->deleted_at);
    }

    #[Test]
    public function is_active_kiem_tra_trang_thai(): void
    {
        $user = User::create([
            'username' => 'testuser',
            'fullname' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Sử dụng fresh để load các giá trị default từ database
        $this->assertTrue($user->fresh()->isActive());

        // Bị khóa → không active
        $user->update(['status' => 'suspended']);
        $this->assertFalse($user->fresh()->isActive());

        // Khôi phục status nhưng xóa mềm → vẫn không active
        $user->update(['status' => 'active']);
        $user->softDelete();
        $this->assertFalse($user->fresh()->isActive());
    }

    #[Test]
    public function is_admin_kiem_tra_quyen(): void
    {
        $user = User::create([
            'username' => 'normaluser',
            'fullname' => 'User',
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $this->assertFalse($user->isAdmin());

        // Gán role admin trực tiếp (bypass fillable — chỉ admin service mới làm)
        $user->role = 'admin';
        $user->save();

        $this->assertTrue($user->fresh()->isAdmin());
    }

    // ===== CASTS =====

    #[Test]
    public function cast_du_lieu_chinh_xac(): void
    {
        $user = User::create([
            'username' => 'castuser',
            'fullname' => 'Test',
            'email' => 'cast@example.com',
            'password' => 'password',
            'settings' => [
                'language' => 'en',
                'theme' => ['mode' => 'dark'],
                'notifications' => ['email' => true],
            ],
        ]);

        $user = $user->fresh();

        // JSON cast thành array
        $this->assertIsArray($user->settings);
        $this->assertSame('en', $user->settings['language']);
        $this->assertSame('dark', $user->settings['theme']['mode']);
        $this->assertTrue($user->settings['notifications']['email']);

        // Boolean cast
        $this->assertIsBool($user->is_deleted);

        // Password tự động hash (không lưu plaintext)
        $this->assertNotSame('password', $user->password);
    }
}
