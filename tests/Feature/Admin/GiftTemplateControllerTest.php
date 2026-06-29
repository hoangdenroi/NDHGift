<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\GiftCategory;
use App\Models\GiftTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test CRUD và các chức năng quản trị Mẫu quà tặng phía Admin.
 */
class GiftTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private GiftCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->category = GiftCategory::factory()->create([
            'name' => 'Tình yêu',
            'slug' => 'tinh-yeu',
        ]);
    }

    // ===== INDEX =====

    /** @test */
    public function admin_xem_danh_sach_gift_templates_thanh_cong(): void
    {
        GiftTemplate::factory()->count(3)->create([
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.gift-templates.index'));

        $response->assertStatus(200);
        $response->assertViewHas('templates');
        $response->assertViewHas('categories');
        $response->assertViewHas('stats');
    }

    /** @test */
    public function loc_gift_templates_theo_filters(): void
    {
        $cat2 = GiftCategory::factory()->create(['name' => 'Sinh nhật', 'slug' => 'sinh-nhat']);

        // Tạo 1 mẫu HOT, thuộc category 1, active
        GiftTemplate::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Trái tim 3D lãng mạn',
            'code' => 'heart_3d',
            'is_hot' => true,
            'is_active' => true,
        ]);

        // Tạo 1 mẫu thường, thuộc category 2, inactive
        GiftTemplate::factory()->create([
            'category_id' => $cat2->id,
            'name' => 'Bánh sinh nhật 3D',
            'code' => 'birthday_cake_3d',
            'is_hot' => false,
            'is_active' => false,
        ]);

        // 1. Lọc theo search
        $response = $this->actingAs($this->admin)->get(route('admin.gift-templates.index', ['search' => 'Trái tim']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('templates'));

        // 2. Lọc theo category
        $response = $this->actingAs($this->admin)->get(route('admin.gift-templates.index', ['category_id' => $cat2->id]));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('templates'));

        // 3. Lọc theo trạng thái active
        $response = $this->actingAs($this->admin)->get(route('admin.gift-templates.index', ['is_active' => '0']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('templates'));

        // 4. Lọc theo HOT
        $response = $this->actingAs($this->admin)->get(route('admin.gift-templates.index', ['is_hot' => '1']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('templates'));
    }

    // ===== STORE =====

    /** @test */
    public function tao_gift_template_moi_thanh_cong(): void
    {
        $templateData = [
            'category_id' => $this->category->id,
            'code' => 'love_letter_3d',
            'name' => 'Thư tình 3D lấp lánh',
            'description' => 'Mô tả thư tình 3D bay bổng',
            'price' => 25000,
            'discount' => 10,
            'is_hot' => true,
            'is_active' => true,
            'demo_url' => '/templates/love_letter_3d/index.html',
            'meta_title' => 'Thư tình 3D đẹp nhất',
            'meta_description' => 'Mẫu thư tình 3D gửi tặng người ấy.',
            'meta_keywords' => 'thư tình 3d, tình yêu',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.gift-templates.store'), $templateData);

        $response->assertRedirect(route('admin.gift-templates.index'));
        $this->assertDatabaseHas('gift_templates', [
            'code' => 'love_letter_3d',
            'name' => 'Thư tình 3D lấp lánh',
            'price' => 25000.00,
            'discount' => 10,
            'is_hot' => true,
            'is_deleted' => false,
        ]);
    }

    /** @test */
    public function khong_tao_gift_template_trung_code(): void
    {
        GiftTemplate::factory()->create([
            'code' => 'heart_3d',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.gift-templates.store'), [
            'category_id' => $this->category->id,
            'code' => 'heart_3d', // Trùng code
            'name' => 'Trái tim mới',
            'price' => 30000,
        ]);

        $response->assertSessionHasErrors('code');
    }

    // ===== UPDATE =====

    /** @test */
    public function cap_nhat_gift_template_thanh_cong(): void
    {
        $template = GiftTemplate::factory()->create([
            'category_id' => $this->category->id,
            'code' => 'old_code',
            'name' => 'Mẫu quà cũ',
            'price' => 15000,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.gift-templates.update', $template->id), [
            'category_id' => $this->category->id,
            'code' => 'new_code',
            'name' => 'Mẫu quà mới',
            'price' => 20000,
            'discount' => 20,
            'is_hot' => false,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.gift-templates.index'));
        $this->assertDatabaseHas('gift_templates', [
            'id' => $template->id,
            'code' => 'new_code',
            'name' => 'Mẫu quà mới',
            'price' => 20000.00,
            'discount' => 20,
        ]);
    }

    // ===== DELETE (SOFT DELETE) =====

    /** @test */
    public function xoa_gift_template_thanh_cong_thuc_hien_xoa_mem(): void
    {
        $template = GiftTemplate::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.gift-templates.destroy', $template->id));

        $response->assertRedirect(route('admin.gift-templates.index'));
        $this->assertDatabaseHas('gift_templates', [
            'id' => $template->id,
            'is_deleted' => true,
        ]);
    }

    // ===== TOGGLE ACTIVE =====

    /** @test */
    public function bat_tat_trang_thai_hoat_dong_gift_template(): void
    {
        $template = GiftTemplate::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.gift-templates.toggle-active', $template->id));

        $response->assertRedirect(route('admin.gift-templates.index'));
        $template->refresh();
        $this->assertFalse($template->is_active);
    }

    // ===== AUTHORIZATION =====

    /** @test */
    public function user_thuong_khong_truy_cap_duoc_gift_templates(): void
    {
        $normalUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($normalUser)->get(route('admin.gift-templates.index'));

        $response->assertStatus(403);
    }
}
