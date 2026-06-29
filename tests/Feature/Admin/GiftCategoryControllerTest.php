<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\GiftCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test CRUD và các chức năng quản lý Danh mục quà tặng phía Admin.
 */
class GiftCategoryControllerTest extends TestCase
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
    public function admin_xem_danh_sach_gift_categories_thanh_cong(): void
    {
        GiftCategory::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.gift-categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('stats');
    }

    /** @test */
    public function loc_gift_categories_theo_trang_thai_va_search(): void
    {
        GiftCategory::factory()->create(['name' => 'Sinh nhật', 'slug' => 'sinh-nhat', 'is_active' => true]);
        GiftCategory::factory()->create(['name' => 'Tình yêu', 'slug' => 'tinh-yeu', 'is_active' => false]);

        // Lọc active
        $response = $this->actingAs($this->admin)->get(route('admin.gift-categories.index', ['is_active' => '1']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('categories'));

        // Search
        $response = $this->actingAs($this->admin)->get(route('admin.gift-categories.index', ['search' => 'Tình']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('categories'));
    }

    // ===== STORE =====

    /** @test */
    public function tao_gift_category_moi_thanh_cong(): void
    {
        $categoryData = [
            'name' => 'Giáng Sinh 2026',
            'slug' => 'giang-sinh-2026',
            'description' => 'Chủ đề Giáng Sinh ấm áp',
            'icon' => 'ac_unit',
            'sort_order' => 5,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.gift-categories.store'), $categoryData);

        $response->assertRedirect(route('admin.gift-categories.index'));
        $this->assertDatabaseHas('gift_categories', [
            'name' => 'Giáng Sinh 2026',
            'slug' => 'giang-sinh-2026',
            'is_deleted' => false
        ]);
    }

    /** @test */
    public function auto_generate_slug_neu_de_trong(): void
    {
        $categoryData = [
            'name' => 'Sinh Nhật 3D',
            'slug' => '',
            'icon' => 'cake',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.gift-categories.store'), $categoryData);

        $response->assertRedirect(route('admin.gift-categories.index'));
        $this->assertDatabaseHas('gift_categories', [
            'name' => 'Sinh Nhật 3D',
            'slug' => 'sinh-nhat-3d'
        ]);
    }

    /** @test */
    public function khong_tao_gift_category_trung_slug(): void
    {
        GiftCategory::factory()->create(['slug' => 'tinh-yeu']);

        $response = $this->actingAs($this->admin)->post(route('admin.gift-categories.store'), [
            'name' => 'Tình Yêu 2',
            'slug' => 'tinh-yeu',
            'icon' => 'favorite',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    /** @test */
    public function validate_slug_dung_dinh_dang_regex(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.gift-categories.store'), [
            'name' => 'Chủ Đề Sai Slug',
            'slug' => 'slug_bi_sai_dinh_dang_!',
            'icon' => 'favorite',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    // ===== UPDATE =====

    /** @test */
    public function cap_nhat_gift_category_thanh_cong(): void
    {
        $category = GiftCategory::factory()->create([
            'name' => 'Chủ đề cũ',
            'slug' => 'chu-de-cu'
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.gift-categories.update', $category), [
            'name' => 'Chủ đề mới',
            'slug' => 'chu-de-moi',
            'icon' => 'star',
            'sort_order' => 10,
        ]);

        $response->assertRedirect(route('admin.gift-categories.index'));
        $this->assertDatabaseHas('gift_categories', [
            'id' => $category->id,
            'name' => 'Chủ đề mới',
            'slug' => 'chu-de-moi',
        ]);
    }

    // ===== DELETE (SOFT DELETE) =====

    /** @test */
    public function xoa_gift_category_thanh_cong_thuc_hien_xoa_mem(): void
    {
        $category = GiftCategory::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.gift-categories.destroy', $category));

        $response->assertRedirect(route('admin.gift-categories.index'));
        
        // Assert is_deleted là true và deleted_at không null
        $this->assertDatabaseHas('gift_categories', [
            'id' => $category->id,
            'is_deleted' => true,
        ]);
    }

    // ===== TOGGLE ACTIVE =====

    /** @test */
    public function bat_tat_trang_thai_gift_category(): void
    {
        $category = GiftCategory::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->patch(route('admin.gift-categories.toggle-active', $category));

        $response->assertRedirect(route('admin.gift-categories.index'));
        $category->refresh();
        $this->assertFalse($category->is_active);
    }

    // ===== AUTHORIZATION =====

    /** @test */
    public function user_thuong_khong_truy_cap_duoc_gift_categories(): void
    {
        $normalUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($normalUser)->get(route('admin.gift-categories.index'));

        $response->assertStatus(403);
    }
}
