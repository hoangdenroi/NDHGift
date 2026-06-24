<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service NotificationService
 *
 * Xử lý các logic nghiệp vụ liên quan đến thông báo của hệ thống.
 */
class NotificationService
{
    /**
     * Lấy danh sách thông báo phân trang của người dùng.
     *
     * @param User $user Người dùng hiện tại
     * @param int $perPage Số lượng phần tử trên mỗi trang
     * @param int $page Số thứ tự trang cần lấy
     * @return LengthAwarePaginator
     */
    public function getNotificationsForUser(User $user, int $perPage = 5, int $page = 1): LengthAwarePaginator
    {
        $deletedBroadcastIds = $user->metadata['deleted_broadcast_ids'] ?? [];

        $paginator = Notification::query()
            ->where(function ($query) use ($user) {
                // Lấy thông báo cá nhân của user
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('scope', 'user');
                })
                // Hoặc lấy thông báo chung (broadcast/system)
                ->orWhere(function ($q) {
                    $q->whereNull('user_id')
                      ->whereIn('scope', ['broadcast', 'system']);
                });
            })
            // Loại bỏ các thông báo đã hết hạn
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            // Loại bỏ thông báo chung đã bị người dùng xóa/ẩn
            ->when(!empty($deletedBroadcastIds), function ($query) use ($deletedBroadcastIds) {
                $query->where(function ($q) use ($deletedBroadcastIds) {
                    $q->where('scope', 'user')
                      ->orWhere(function ($sub) use ($deletedBroadcastIds) {
                          $sub->whereIn('scope', ['broadcast', 'system'])
                              ->whereNotIn('id', $deletedBroadcastIds);
                      });
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $readBroadcastIds = $user->metadata['read_broadcast_ids'] ?? [];

        // Chuyển đổi dữ liệu các thông báo để trả về API thân thiện hơn
        $formattedItems = collect($paginator->items())->map(function ($noti) use ($readBroadcastIds) {
            // Xác định trạng thái đã đọc động cho thông báo chung
            $isRead = $noti->scope === 'user'
                ? (bool) $noti->is_read
                : in_array($noti->id, $readBroadcastIds, true);

            return [
                'id' => $noti->id,
                'title' => $noti->title,
                'message' => $noti->message,
                'type' => $noti->type ?? 'info',
                'action_url' => $noti->action_url,
                'is_read' => $isRead,
                'created_at' => $noti->created_at->toIso8601String(),
                'created_at_human' => $noti->created_at->diffForHumans(),
            ];
        });

        // Trả về đối tượng LengthAwarePaginator mới chứa dữ liệu đã được định dạng
        return new LengthAwarePaginator(
            $formattedItems,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Lấy tổng số thông báo chưa đọc của người dùng.
     *
     * @param User $user Người dùng hiện tại
     * @return int
     */
    public function getUnreadCountForUser(User $user): int
    {
        // 1. Đếm số thông báo cá nhân chưa đọc
        $unreadPersonalCount = Notification::query()
            ->where('user_id', $user->id)
            ->where('scope', 'user')
            ->where('is_read', false)
            ->count();

        // 2. Đếm số thông báo chung chưa đọc và chưa bị ẩn
        $deletedBroadcastIds = $user->metadata['deleted_broadcast_ids'] ?? [];
        $readBroadcastIds = $user->metadata['read_broadcast_ids'] ?? [];

        $unreadBroadcastCount = Notification::query()
            ->whereNull('user_id')
            ->whereIn('scope', ['broadcast', 'system'])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->when(!empty($deletedBroadcastIds), function ($query) use ($deletedBroadcastIds) {
                $query->whereNotIn('id', $deletedBroadcastIds);
            })
            ->when(!empty($readBroadcastIds), function ($query) use ($readBroadcastIds) {
                $query->whereNotIn('id', $readBroadcastIds);
            })
            ->count();

        return $unreadPersonalCount + $unreadBroadcastCount;
    }

    /**
     * Đánh dấu một thông báo là đã đọc.
     *
     * @param User $user Người dùng thực hiện thao tác
     * @param int $notificationId ID thông báo
     * @return bool
     */
    public function markAsRead(User $user, int $notificationId): bool
    {
        $noti = Notification::query()->find($notificationId);
        if (!$noti) {
            return false;
        }

        // Nếu là thông báo cá nhân của user
        if ($noti->scope === 'user' && (int) $noti->user_id === (int) $user->id) {
            $noti->is_read = true;
            $noti->read_at = now();
            return $noti->save();
        }

        // Nếu là thông báo chung (broadcast/system)
        if ($noti->user_id === null && in_array($noti->scope, ['broadcast', 'system'], true)) {
            $metadata = $user->metadata ?? [];
            $readBroadcastIds = $metadata['read_broadcast_ids'] ?? [];

            if (!in_array($noti->id, $readBroadcastIds, true)) {
                $readBroadcastIds[] = $noti->id;
                $metadata['read_broadcast_ids'] = $readBroadcastIds;
                $user->metadata = $metadata;
                return $user->save();
            }
            return true;
        }

        return false;
    }

    /**
     * Đánh dấu toàn bộ thông báo hiện có là đã đọc.
     *
     * @param User $user Người dùng thực hiện thao tác
     * @return bool
     */
    public function markAllAsRead(User $user): bool
    {
        // 1. Đánh dấu đã đọc tất cả thông báo riêng
        Notification::query()
            ->where('user_id', $user->id)
            ->where('scope', 'user')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // 2. Thêm tất cả ID của thông báo chung hiện tại vào metadata['read_broadcast_ids']
        $deletedBroadcastIds = $user->metadata['deleted_broadcast_ids'] ?? [];

        $broadcastIds = Notification::query()
            ->whereNull('user_id')
            ->whereIn('scope', ['broadcast', 'system'])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->when(!empty($deletedBroadcastIds), function ($query) use ($deletedBroadcastIds) {
                $query->whereNotIn('id', $deletedBroadcastIds);
            })
            ->pluck('id')
            ->toArray();

        $metadata = $user->metadata ?? [];
        $readBroadcastIds = $metadata['read_broadcast_ids'] ?? [];

        $newReadBroadcastIds = array_values(array_unique(array_merge($readBroadcastIds, $broadcastIds)));

        $metadata['read_broadcast_ids'] = $newReadBroadcastIds;
        $user->metadata = $metadata;
        
        return $user->save();
    }

    /**
     * Xóa toàn bộ thông báo (Xóa thông báo riêng và ẩn thông báo chung).
     *
     * @param User $user Người dùng thực hiện thao tác
     * @return bool
     */
    public function clearAllNotifications(User $user): bool
    {
        // 1. Xóa vật lý các thông báo riêng của tài khoản khỏi database
        Notification::query()
            ->where('user_id', $user->id)
            ->where('scope', 'user')
            ->delete();

        // 2. Thêm toàn bộ thông báo chung hiện tại vào metadata['deleted_broadcast_ids'] để ẩn đi
        $broadcastIds = Notification::query()
            ->whereNull('user_id')
            ->whereIn('scope', ['broadcast', 'system'])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->pluck('id')
            ->toArray();

        $metadata = $user->metadata ?? [];
        $deletedBroadcastIds = $metadata['deleted_broadcast_ids'] ?? [];

        $newDeletedBroadcastIds = array_values(array_unique(array_merge($deletedBroadcastIds, $broadcastIds)));

        $metadata['deleted_broadcast_ids'] = $newDeletedBroadcastIds;
        $user->metadata = $metadata;

        return $user->save();
    }
}
