<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test CRUD và các chức năng quản lý Coupon phía Admin.
 */
class CouponControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    // ===== INDEX =====

    /** @test */
    public function admin_xem_danh_sach_coupon_thanh_cong(): void
    {
        Coupon::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.coupons.index'));

        $response->assertStatus(200);
        $response->assertViewHas('coupons');
        $response->assertViewHas('stats');
    }

    /** @test */
    public function loc_coupon_theo_loai(): void
    {
        Coupon::factory()->create(['type' => 'percent']);
        Coupon::factory()->create(['type' => 'fixed']);

        $response = $this->actingAs($this->admin)->get(route('admin.coupons.index', ['type' => 'percent']));

        $response->assertStatus(200);
    }

    // ===== STORE =====

    /** @test */
    public function tao_coupon_moi_thanh_cong(): void
    {
        $couponData = [
            'code' => 'GIAM50K',
            'type' => 'fixed',
            'value' => 50000,
            'status' => 'public',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.coupons.store'), $couponData);

        $response->assertRedirect(route('admin.coupons.index'));
        $this->assertDatabaseHas('coupons', ['code' => 'GIAM50K', 'type' => 'fixed']);
    }

    /** @test */
    public function auto_uppercase_ma_code(): void
    {
        $couponData = [
            'code' => 'giam30k',
            'type' => 'fixed',
            'value' => 30000,
            'status' => 'public',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.coupons.store'), $couponData);

        $response->assertRedirect(route('admin.coupons.index'));
        // prepareForValidation tự uppercase
        $this->assertDatabaseHas('coupons', ['code' => 'GIAM30K']);
    }

    /** @test */
    public function khong_tao_coupon_trung_code(): void
    {
        Coupon::factory()->create(['code' => 'DUPLICATE']);

        $response = $this->actingAs($this->admin)->post(route('admin.coupons.store'), [
            'code' => 'DUPLICATE',
            'type' => 'fixed',
            'value' => 10000,
            'status' => 'public',
        ]);

        $response->assertSessionHasErrors('code');
    }

    /** @test */
    public function validate_expires_at_phai_sau_starts_at(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.coupons.store'), [
            'code' => 'WRONGDATE',
            'type' => 'fixed',
            'value' => 10000,
            'starts_at' => '2026-12-31 00:00:00',
            'expires_at' => '2026-01-01 00:00:00',
            'status' => 'public',
        ]);

        $response->assertSessionHasErrors('expires_at');
    }

    // ===== UPDATE =====

    /** @test */
    public function cap_nhat_coupon_thanh_cong(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'OLD_CODE', 'value' => 10000]);

        $response = $this->actingAs($this->admin)->put(route('admin.coupons.update', $coupon), [
            'code' => 'NEW_CODE',
            'type' => 'fixed',
            'value' => 20000,
            'status' => 'public',
        ]);

        $response->assertRedirect(route('admin.coupons.index'));
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'code' => 'NEW_CODE']);
    }

    // ===== DELETE =====

    /** @test */
    public function xoa_coupon_thanh_cong(): void
    {
        $coupon = Coupon::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.coupons.destroy', $coupon));

        $response->assertRedirect(route('admin.coupons.index'));
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    // ===== TOGGLE ACTIVE =====

    /** @test */
    public function bat_tat_trang_thai_coupon(): void
    {
        $coupon = Coupon::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->patch(route('admin.coupons.toggle-active', $coupon));

        $response->assertRedirect(route('admin.coupons.index'));
        $coupon->refresh();
        $this->assertFalse($coupon->is_active);
    }

    // ===== AUTHORIZATION =====

    /** @test */
    public function user_thuong_khong_truy_cap_duoc(): void
    {
        $normalUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($normalUser)->get(route('admin.coupons.index'));

        $response->assertStatus(403);
    }
}
