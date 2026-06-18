<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm tra AuditLogService:
 * - Ghi log thành công với đầy đủ thông tin
 * - Tự ẩn field nhạy cảm (password, token)
 * - Tự truncate chuỗi dài
 * - Login/Logout tự tạo audit log
 */
class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ghi_log_thanh_cong_voi_day_du_thong_tin(): void
    {
        $user = User::factory()->create(['status' => 'active', 'is_deleted' => false]);

        $log = AuditLogService::log(
            'test_action',
            $user,
            ['name' => 'Tên cũ'],
            ['name' => 'Tên mới'],
            $user->id
        );

        $this->assertNotNull($log);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'test_action',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        // Kiểm tra old_values và new_values được lưu đúng
        $this->assertEquals(['name' => 'Tên cũ'], $log->old_values);
        $this->assertEquals(['name' => 'Tên mới'], $log->new_values);
    }

    public function test_an_field_nhay_cam_password_va_token(): void
    {
        $user = User::factory()->create(['status' => 'active', 'is_deleted' => false]);

        $log = AuditLogService::log(
            'update_profile',
            $user,
            null,
            ['name' => 'Hoàng', 'password' => 'secret123', 'token' => 'abc-xyz-123'],
            $user->id
        );

        $this->assertNotNull($log);
        // password và token phải bị ẩn — không được lưu giá trị thật vào DB
        $this->assertEquals('********', $log->new_values['password']);
        $this->assertEquals('********', $log->new_values['token']);
        // name không bị ảnh hưởng
        $this->assertEquals('Hoàng', $log->new_values['name']);
    }

    public function test_truncate_chuoi_dai_hon_200_ky_tu(): void
    {
        $user = User::factory()->create(['status' => 'active', 'is_deleted' => false]);
        $longString = str_repeat('A', 300);

        $log = AuditLogService::log(
            'test_truncate',
            null,
            null,
            ['content' => $longString],
            $user->id
        );

        $this->assertNotNull($log);
        // Chuỗi 300 ký tự phải bị cắt xuống 200 + "... [truncated]"
        $this->assertStringEndsWith('... [truncated]', $log->new_values['content']);
        $this->assertLessThan(300, mb_strlen($log->new_values['content']));
    }

    public function test_ghi_log_khong_co_model(): void
    {
        $user = User::factory()->create(['status' => 'active', 'is_deleted' => false]);

        $log = AuditLogService::log('custom_action', null, null, null, $user->id);

        $this->assertNotNull($log);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'custom_action',
            'model_type' => null,
            'model_id' => null,
        ]);
    }

    public function test_login_tu_dong_ghi_audit_log(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_deleted' => false,
            'password' => bcrypt('password123'),
        ]);

        $this->post('/en/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // Sau khi login thành công, phải có audit log action = 'login'
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);
    }

    public function test_logout_tu_dong_ghi_audit_log(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_deleted' => false,
        ]);

        // Đăng nhập trước
        $this->actingAs($user);

        // Thực hiện logout
        $this->post('/en/logout');

        // Phải có audit log action = 'logout'
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'logout',
        ]);
    }
}
