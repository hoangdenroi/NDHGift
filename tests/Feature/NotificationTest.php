<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class NotificationTest
 *
 * Kiểm tra các chức năng API liên quan đến thông báo của người dùng.
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Khởi tạo user mẫu cho mỗi ca test
        $this->user = User::factory()->create([
            'username' => 'test_user',
            'fullname' => 'Test User',
            'email' => 'testuser@ndhgift.com',
            'metadata' => null,
        ]);
    }

    /**
     * Test case: Lấy danh sách thông báo phân trang và số lượng chưa đọc.
     */
    public function test_get_notifications_paginated(): void
    {
        // Tạo 3 thông báo cá nhân
        Notification::create([
            'user_id' => $this->user->id,
            'scope' => 'user',
            'title' => 'Thông báo cá nhân 1',
            'message' => 'Nội dung thông báo cá nhân 1',
            'is_read' => false,
        ]);
        Notification::create([
            'user_id' => $this->user->id,
            'scope' => 'user',
            'title' => 'Thông báo cá nhân 2',
            'message' => 'Nội dung thông báo cá nhân 2',
            'is_read' => true, // Đã đọc
        ]);
        Notification::create([
            'user_id' => $this->user->id,
            'scope' => 'user',
            'title' => 'Thông báo cá nhân 3',
            'message' => 'Nội dung thông báo cá nhân 3',
            'is_read' => false,
        ]);

        // Tạo 2 thông báo chung
        Notification::create([
            'user_id' => null,
            'scope' => 'broadcast',
            'title' => 'Thông báo chung 1',
            'message' => 'Nội dung thông báo chung 1',
            'is_read' => false,
        ]);
        Notification::create([
            'user_id' => null,
            'scope' => 'system',
            'title' => 'Thông báo hệ thống 1',
            'message' => 'Nội dung thông báo hệ thống 1',
            'is_read' => false,
        ]);

        // Gọi API lấy danh sách phân trang (2 phần tử trên trang)
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/notifications?per_page=2&page=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'current_page' => 1,
                'per_page' => 2,
                'total' => 5,
                // Số lượng chưa đọc = 2 cá nhân (1 & 3) + 2 chung (broadcast & system) = 4
                'unread_count' => 4,
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test case: Đánh dấu một thông báo cá nhân là đã đọc.
     */
    public function test_mark_personal_notification_as_read(): void
    {
        $noti = Notification::create([
            'user_id' => $this->user->id,
            'scope' => 'user',
            'title' => 'Thông báo cá nhân chưa đọc',
            'message' => 'Nội dung test',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/notifications/{$noti->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        // Xác minh trạng thái đã cập nhật trong database
        $this->assertTrue($noti->fresh()->is_read);
        $this->assertNotNull($noti->fresh()->read_at);
    }

    /**
     * Test case: Đánh dấu một thông báo chung (broadcast) là đã đọc.
     */
    public function test_mark_broadcast_notification_as_read(): void
    {
        $noti = Notification::create([
            'user_id' => null,
            'scope' => 'broadcast',
            'title' => 'Thông báo chung',
            'message' => 'Nội dung test',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/notifications/{$noti->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        // Xác minh thông báo đã được lưu ID vào metadata của user
        $freshUser = $this->user->fresh();
        $this->assertContains($noti->id, $freshUser->metadata['read_broadcast_ids']);

        // Xác minh khi lấy danh sách, thông báo chung này trả về is_read = true
        $listResponse = $this->actingAs($this->user)
            ->getJson('/api/v1/notifications');
        
        $listResponse->assertStatus(200);
        $data = $listResponse->json('data');
        $this->assertEquals($noti->id, $data[0]['id']);
        $this->assertTrue($data[0]['is_read']);
    }

    /**
     * Test case: Đánh dấu tất cả thông báo là đã đọc.
     */
    public function test_mark_all_notifications_as_read(): void
    {
        // 2 cá nhân chưa đọc
        $noti1 = Notification::create([
            'user_id' => $this->user->id,
            'scope' => 'user',
            'title' => 'Cá nhân 1',
            'is_read' => false,
        ]);
        $noti2 = Notification::create([
            'user_id' => $this->user->id,
            'scope' => 'user',
            'title' => 'Cá nhân 2',
            'is_read' => false,
        ]);

        // 1 chung chưa đọc
        $noti3 = Notification::create([
            'user_id' => null,
            'scope' => 'broadcast',
            'title' => 'Chung 1',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        // Xác minh thông báo cá nhân đã cập nhật trong DB
        $this->assertTrue($noti1->fresh()->is_read);
        $this->assertTrue($noti2->fresh()->is_read);

        // Xác minh thông báo chung đã được ghi nhận trong metadata của user
        $freshUser = $this->user->fresh();
        $this->assertContains($noti3->id, $freshUser->metadata['read_broadcast_ids']);
    }

    /**
     * Test case: Xóa toàn bộ thông báo (xóa cá nhân khỏi DB và ẩn thông báo chung).
     */
    public function test_clear_all_notifications(): void
    {
        // Thông báo cá nhân
        $noti1 = Notification::create([
            'user_id' => $this->user->id,
            'scope' => 'user',
            'title' => 'Cá nhân',
            'is_read' => false,
        ]);

        // Thông báo chung
        $noti2 = Notification::create([
            'user_id' => null,
            'scope' => 'broadcast',
            'title' => 'Chung',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/notifications/clear-all');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        // Xác minh thông báo cá nhân bị xóa hoàn toàn khỏi DB
        $this->assertDatabaseMissing('notifications', [
            'id' => $noti1->id,
        ]);

        // Xác minh thông báo chung vẫn tồn tại trong DB nhưng ID được ghi nhận vào deleted_broadcast_ids của user
        $this->assertDatabaseHas('notifications', [
            'id' => $noti2->id,
        ]);
        $freshUser = $this->user->fresh();
        $this->assertContains($noti2->id, $freshUser->metadata['deleted_broadcast_ids']);

        // Gọi API lấy danh sách và đảm bảo kết quả trả về trống (không có thông báo nào hiển thị)
        $listResponse = $this->actingAs($this->user)
            ->getJson('/api/v1/notifications');

        $listResponse->assertStatus(200);
        $this->assertCount(0, $listResponse->json('data'));
    }
}
