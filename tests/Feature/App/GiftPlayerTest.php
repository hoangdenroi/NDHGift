<?php

declare(strict_types=1);

namespace Tests\Feature\App;

use App\Models\GiftCategory;
use App\Models\GiftTemplate;
use App\Models\GiftDurationPlan;
use App\Models\User;
use App\Models\UserGift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử tính năng Trình phát quà tặng (Gift Player) và trang Demo.
 */
class GiftPlayerTest extends TestCase
{
    use RefreshDatabase;

    private GiftCategory $category;
    private GiftTemplate $template;
    private GiftDurationPlan $durationPlan;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo Category
        $this->category = GiftCategory::create([
            'name' => 'Tình yêu',
            'slug' => 'tinh-yeu',
            'description' => 'Mẫu quà tình yêu lãng mạn',
            'icon' => 'favorite',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 2. Tạo Template
        $this->template = GiftTemplate::create([
            'category_id' => $this->category->id,
            'code' => 'heart_3d',
            'name' => 'Trái Tim 3D lấp lánh',
            'description' => 'Mẫu quà 3D trái tim xoay',
            'price' => 50000.00,
            'discount' => 0,
            'is_active' => true,
            'form_schema' => [
                'fields' => [
                    [
                        'name' => 'receiver_name',
                        'type' => 'text',
                        'default' => 'Người Nhận Mẫu'
                    ],
                    [
                        'name' => 'sender_name',
                        'type' => 'text',
                        'default' => 'Người Gửi Mẫu'
                    ],
                    [
                        'name' => 'title' ,
                        'type' => 'text',
                        'default' => 'Tiêu Đề Mẫu'
                    ],
                    [
                        'name' => 'message',
                        'type' => 'textarea',
                        'default' => 'Lời nhắn mẫu yêu thương'
                    ]
                ]
            ]
        ]);

        // 3. Tạo Duration Plan
        $this->durationPlan = GiftDurationPlan::create([
            'name' => '30 ngày',
            'code' => '30_days',
            'duration_days' => 30,
            'price' => 10000.00,
            'description' => 'Gói thời hạn 30 ngày',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 4. Tạo User
        $this->user = User::factory()->create();
    }

    /**
     * Test khách có thể xem quà tặng hợp lệ (đã thanh toán, chưa hết hạn, không hẹn giờ).
     */
    public function test_guest_can_play_active_gift_by_slug(): void
    {
        $gift = UserGift::create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'duration_plan_id' => $this->durationPlan->id,
            'slug' => 'test-gift-slug-123',
            'status' => UserGift::STATUS_PAID,
            'content_data' => [
                'receiver_name' => 'Mỹ Duyên',
                'sender_name' => 'Khánh Toàn',
                'title' => 'Happy Anniversary! ❤️',
                'message' => '5 năm bên nhau thật tuyệt vời. Yêu em!',
                'settings' => [
                    'music_url' => 'https://assets.mixkit.co/music/preview/mixkit-beautiful-dream-493.mp3',
                    'spiral_texts' => ['Mỹ Duyên 💖', 'Khánh Toàn 💕']
                ]
            ],
            'expires_at' => now()->addDays(30),
            'scheduled_at' => null
        ]);

        $response = $this->get("/gift/{$gift->slug}");

        $response->assertStatus(200);
        $response->assertSee('Mỹ Duyên');
        $response->assertSee('Khánh Toàn');
        $response->assertSee('Happy Anniversary! ❤️');
        $response->assertSee('5 năm bên nhau thật tuyệt vời. Yêu em!');
        $response->assertDontSee('DEMO MODE');

        // Lượt xem được tăng
        $gift->refresh();
        $this->assertEquals(1, $gift->view_count);
    }

    /**
     * Test không thể xem quà tặng ở trạng thái nháp (chưa thanh toán).
     */
    public function test_cannot_play_draft_gift(): void
    {
        $gift = UserGift::create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'duration_plan_id' => $this->durationPlan->id,
            'slug' => 'test-gift-draft',
            'status' => UserGift::STATUS_DRAFT,
            'content_data' => [
                'receiver_name' => 'Mỹ Duyên',
                'sender_name' => 'Khánh Toàn',
                'title' => 'Draft',
                'message' => 'Draft Message',
            ],
        ]);

        $response = $this->get("/gift/{$gift->slug}");

        $response->assertStatus(404);
    }

    /**
     * Test không thể xem quà tặng đã hết hạn sử dụng.
     */
    public function test_cannot_play_expired_gift(): void
    {
        $gift = UserGift::create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'duration_plan_id' => $this->durationPlan->id,
            'slug' => 'test-gift-expired',
            'status' => UserGift::STATUS_PAID,
            'content_data' => [
                'receiver_name' => 'Mỹ Duyên',
                'sender_name' => 'Khánh Toàn',
                'title' => 'Expired',
                'message' => 'Expired Message',
            ],
            'expires_at' => now()->subHour() // Đã hết hạn trước 1 tiếng
        ]);

        $response = $this->get("/gift/{$gift->slug}");

        $response->assertStatus(403);
        $response->assertSee('Món Quà Đã Hết Hạn');
    }

    /**
     * Test quà hẹn giờ hiển thị trang đếm ngược nếu chưa tới thời điểm mở khóa.
     */
    public function test_cannot_play_before_scheduled_time(): void
    {
        $gift = UserGift::create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'duration_plan_id' => $this->durationPlan->id,
            'slug' => 'test-gift-scheduled',
            'status' => UserGift::STATUS_PAID,
            'content_data' => [
                'receiver_name' => 'Mỹ Duyên',
                'sender_name' => 'Khánh Toàn',
                'title' => 'Hẹn giờ',
                'message' => 'Hẹn giờ Message',
            ],
            'scheduled_at' => now()->addDays(2) // Hẹn giờ sau 2 ngày nữa mới được mở
        ]);

        $response = $this->get("/gift/{$gift->slug}");

        $response->assertStatus(200);
        $response->assertSee('Món Quà Đang Được Hẹn Giờ Gửi!');
        $response->assertSee('Mỹ Duyên');
    }

    /**
     * Test nhà phát triển/người dùng có thể truy cập trang demo.
     */
    public function test_dev_can_access_gift_demo(): void
    {
        $response = $this->get("/gift/demo/{$this->template->code}");

        $response->assertStatus(200);
        $response->assertSee('DEMO MODE');
        $response->assertSee('Người Nhận Mẫu');
        $response->assertSee('Người Gửi Mẫu');
        $response->assertSee('Tiêu Đề Mẫu');
        $response->assertSee('Lời nhắn mẫu yêu thương');
    }

    /**
     * Test nhà phát triển/người dùng có thể truy cập trang demo winter_3d.
     */
    public function test_dev_can_access_winter_gift_demo(): void
    {
        // Tạo template winter_3d
        GiftTemplate::create([
            'category_id' => $this->category->id,
            'code' => 'winter_3d',
            'name' => 'Mùa Đông Tuyết Rơi 3D – Album Kỷ Niệm ❄️',
            'description' => 'Mô tả',
            'price' => 49999.00,
            'discount' => 20,
            'is_active' => true,
            'form_schema' => [
                'fields' => [
                    [
                        'name' => 'receiver_name',
                        'type' => 'text',
                        'default' => 'Người Nhận Mùa Đông'
                    ],
                    [
                        'name' => 'sender_name',
                        'type' => 'text',
                        'default' => 'Người Gửi Mùa Đông'
                    ]
                ]
            ]
        ]);

        $response = $this->get('/gift/demo/winter_3d');

        $response->assertStatus(200);
        $response->assertSee('DEMO MODE');
        $response->assertSee('Người Nhận Mùa Đông');
        $response->assertSee('Người Gửi Mùa Đông');
        $response->assertSee('Cuộn chuột hoặc kéo màn hình để tham quan album');
    }
}
