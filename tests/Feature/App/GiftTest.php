<?php

declare(strict_types=1);

namespace Tests\Feature\App;

use App\Models\GiftCategory;
use App\Models\GiftTemplate;
use App\Services\GiftService;
use App\Services\Admin\GiftTemplateAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Kiểm thử tính năng hiển thị trang Quà tặng và cơ chế cache danh mục.
 */
class GiftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Đảm bảo cache sạch trước mỗi test case
        Cache::forget('gift_categories_active');
        Cache::forget(GiftService::CACHE_KEY);
    }

    /**
     * Kiểm tra truy cập trang Quà tặng và đảm bảo cơ chế cache danh mục hoạt động chính xác.
     */
    public function test_gift_categories_are_cached_and_cleared_on_model_changes(): void
    {
        // 1. Tạo dữ liệu danh mục mẫu
        $category = GiftCategory::create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'description' => 'Original Description',
            'icon' => 'cake',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 2. Lần truy cập đầu tiên - Dữ liệu sẽ được load vào cache
        $response = $this->get('/vi/apps/gift');
        $response->assertStatus(200);
        $response->assertSee('Original Name');

        // Kiểm tra xem cache key đã tồn tại trong cache chưa
        $this->assertTrue(Cache::has('gift_categories_active'));

        // 3. Cập nhật trực tiếp trong CSDL (bypass Eloquent events) để kiểm tra cache thực sự được dùng
        DB::table('gift_categories')
            ->where('id', $category->id)
            ->update(['name' => 'Bypassed Name']);

        // Truy cập lại - Vẫn phải thấy tên cũ 'Original Name' do cache chưa bị xóa
        $response2 = $this->get('/vi/apps/gift');
        $response2->assertStatus(200);
        $response2->assertSee('Original Name');
        $response2->assertDontSee('Bypassed Name');

        // 4. Cập nhật qua Eloquent Model để trigger event 'saved' và clear cache
        $category->refresh();
        $category->name = 'Eloquent Updated Name';
        $category->save(); // Sẽ trigger event saved -> xóa cache 'gift_categories_active'

        // Kiểm tra cache đã bị xóa
        $this->assertFalse(Cache::has('gift_categories_active'));

        // Truy cập lại - Phải thấy tên mới 'Eloquent Updated Name'
        $response3 = $this->get('/vi/apps/gift');
        $response3->assertStatus(200);
        $response3->assertSee('Eloquent Updated Name');
        $response3->assertDontSee('Original Name');
    }

    /**
     * Kiểm tra lấy danh sách quà tặng (Gift templates) hoạt động chính xác và có cache.
     */
    public function test_gift_templates_are_cached_and_displayed_on_gift_page(): void
    {
        $category = GiftCategory::create([
            'name' => 'Love Category',
            'slug' => 'love',
            'description' => 'Love gifts',
            'icon' => 'favorite',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $giftTemplate = GiftTemplate::create([
            'category_id' => $category->id,
            'code' => 'heart_3d',
            'name' => 'Web Heart 3D',
            'description' => 'Mô tả mẫu trái tim 3D',
            'price' => 39999.00,
            'discount' => 10,
            'sold' => 50,
            'stars' => 100,
            'is_hot' => true,
            'is_active' => true,
        ]);

        // Lần truy cập đầu tiên - Load vào cache
        $response = $this->get('/vi/apps/gift');
        $response->assertStatus(200);
        $response->assertSee('Web Heart 3D');

        // Kiểm tra cache đã được lưu
        $this->assertTrue(Cache::has(GiftService::CACHE_KEY));

        // Cập nhật trong DB (bypass cache)
        DB::table('gift_templates')
            ->where('id', $giftTemplate->id)
            ->update(['name' => 'Name Changed Directly']);

        // Truy cập lại - Vẫn nhận giá trị từ cache (tên cũ)
        $response2 = $this->get('/vi/apps/gift');
        $response2->assertStatus(200);
        $response2->assertSee('Web Heart 3D');
        $response2->assertDontSee('Name Changed Directly');
    }

    /**
     * Kiểm tra cache của mẫu quà tặng được xóa khi admin thực hiện các thay đổi.
     */
    public function test_gift_templates_cache_is_cleared_on_admin_changes(): void
    {
        $category = GiftCategory::create([
            'name' => 'Love Category',
            'slug' => 'love',
            'description' => 'Love gifts',
            'icon' => 'favorite',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $giftTemplate = GiftTemplate::create([
            'category_id' => $category->id,
            'code' => 'heart_3d',
            'name' => 'Web Trái Tim 3D',
            'description' => 'Mô tả',
            'price' => 39999.00,
            'discount' => 10,
            'sold' => 50,
            'stars' => 100,
            'is_hot' => true,
            'is_active' => true,
        ]);

        // Tạo cache
        $giftService = new GiftService();
        $giftService->getActiveTemplatesForClient();
        $this->assertTrue(Cache::has(GiftService::CACHE_KEY));

        // 1. Thử cập nhật qua Admin Service -> cache phải bị xóa
        $adminService = new GiftTemplateAdminService();
        $adminService->update($giftTemplate->id, ['name' => 'Cập nhật qua Admin']);
        $this->assertFalse(Cache::has(GiftService::CACHE_KEY));

        // Tạo lại cache
        $giftService->getActiveTemplatesForClient();
        $this->assertTrue(Cache::has(GiftService::CACHE_KEY));

        // 2. Thử bật/tắt kích hoạt qua Admin Service -> cache phải bị xóa
        $adminService->toggleActive($giftTemplate->id);
        $this->assertFalse(Cache::has(GiftService::CACHE_KEY));

        // Tạo lại cache
        $giftService->getActiveTemplatesForClient();
        $this->assertTrue(Cache::has(GiftService::CACHE_KEY));

        // 3. Thử tạo mới qua Admin Service -> cache phải bị xóa
        $adminService->create([
            'category_id' => $category->id,
            'code' => 'new_gift_code',
            'name' => 'Mẫu quà tặng mới',
            'price' => 50000.00,
            'discount' => 0,
        ]);
        $this->assertFalse(Cache::has(GiftService::CACHE_KEY));

        // Tạo lại cache
        $giftService->getActiveTemplatesForClient();
        $this->assertTrue(Cache::has(GiftService::CACHE_KEY));

        // 4. Thử xóa mềm qua Admin Service -> cache phải bị xóa
        $adminService->softDelete($giftTemplate->id);
        $this->assertFalse(Cache::has(GiftService::CACHE_KEY));
    }

    /**
     * Kiểm tra khách (guest) truy cập trang danh sách quà tặng.
     */
    public function test_guest_can_access_gift_page(): void
    {
        $response = $this->get('/vi/apps/gift');
        $response->assertStatus(200);
        $response->assertSee('NDHGift');
    }

    /**
     * Kiểm tra người dùng đã đăng nhập truy cập trang danh sách quà tặng.
     */
    public function test_authenticated_user_can_access_gift_page(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/vi/apps/gift');
        $response->assertStatus(200);
        $response->assertSee('NDHGift');
    }

    /**
     * Kiểm tra khách (guest) truy cập trang chi tiết quà tặng hợp lệ.
     */
    public function test_guest_can_access_gift_detail_page(): void
    {
        $category = GiftCategory::create([
            'name' => 'Love Category',
            'slug' => 'love',
            'description' => 'Love gifts',
            'icon' => 'favorite',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $giftTemplate = GiftTemplate::create([
            'category_id' => $category->id,
            'code' => 'heart_3d',
            'name' => 'Web Heart 3D Detail Test',
            'description' => 'Mô tả chi tiết mẫu trái tim 3D',
            'price' => 39999.00,
            'discount' => 10,
            'sold' => 50,
            'stars' => 100,
            'is_hot' => true,
            'is_active' => true,
        ]);

        $response = $this->get("/vi/apps/gift/{$giftTemplate->unitcode}");
        $response->assertStatus(200);
        $response->assertSee('Web Heart 3D Detail Test');
        $response->assertSee('Mô tả chi tiết mẫu trái tim 3D');
        // Vì chưa đăng nhập, nút hành động phải hiển thị đăng nhập để tạo
        $response->assertSee('Đăng nhập để tạo');
        $response->assertDontSee('Tạo quà ngay');
    }

    /**
     * Kiểm tra người dùng đã đăng nhập truy cập trang chi tiết quà tặng và thấy nút tạo ngay.
     */
    public function test_authenticated_user_can_access_gift_detail_page_and_sees_create_button(): void
    {
        $user = \App\Models\User::factory()->create();

        $category = GiftCategory::create([
            'name' => 'Love Category',
            'slug' => 'love',
            'description' => 'Love gifts',
            'icon' => 'favorite',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $giftTemplate = GiftTemplate::create([
            'category_id' => $category->id,
            'code' => 'heart_3d',
            'name' => 'Web Heart 3D Detail Test',
            'description' => 'Mô tả chi tiết mẫu trái tim 3D',
            'price' => 39999.00,
            'discount' => 10,
            'sold' => 50,
            'stars' => 100,
            'is_hot' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get("/vi/apps/gift/{$giftTemplate->unitcode}");
        $response->assertStatus(200);
        $response->assertSee('Tạo quà ngay');
        $response->assertDontSee('Đăng nhập để tạo');
    }

    /**
     * Kiểm tra truy cập trang chi tiết quà tặng trả về 404 nếu template bị ẩn hoặc xóa mềm.
     */
    public function test_gift_detail_page_returns_404_for_inactive_or_deleted_template(): void
    {
        $category = GiftCategory::create([
            'name' => 'Love Category',
            'slug' => 'love',
            'description' => 'Love gifts',
            'icon' => 'favorite',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Trường hợp 1: Template không active
        $giftTemplateInactive = GiftTemplate::create([
            'category_id' => $category->id,
            'code' => 'heart_3d_inactive',
            'name' => 'Web Heart 3D Inactive',
            'price' => 39999.00,
            'is_active' => false,
        ]);

        $response = $this->get("/vi/apps/gift/{$giftTemplateInactive->unitcode}");
        $response->assertStatus(404);

        // Trường hợp 2: Template bị xóa mềm
        $giftTemplateDeleted = GiftTemplate::create([
            'category_id' => $category->id,
            'code' => 'heart_3d_deleted',
            'name' => 'Web Heart 3D Deleted',
            'price' => 39999.00,
            'is_active' => true,
        ]);
        $giftTemplateDeleted->is_deleted = true;
        $giftTemplateDeleted->save();

        $response2 = $this->get("/vi/apps/gift/{$giftTemplateDeleted->unitcode}");
        $response2->assertStatus(404);
    }
}

