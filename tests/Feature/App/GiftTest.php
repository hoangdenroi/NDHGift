<?php

declare(strict_types=1);

namespace Tests\Feature\App;

use App\Models\GiftCategory;
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
}
