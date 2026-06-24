<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\notification;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller NotificationController
 *
 * Quản lý các yêu cầu API về thông báo của người dùng.
 */
class NotificationController extends Controller
{
    /**
     * Khởi tạo Controller với NotificationService.
     *
     * @param NotificationService $notificationService
     */
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Lấy danh sách thông báo phân trang của người dùng hiện tại.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $page = (int) $request->query('page', 1);
        // Mặc định dropdown là 5, panel là 10
        $perPage = (int) $request->query('per_page', 5);

        try {
            $paginator = $this->notificationService->getNotificationsForUser($user, $perPage, $page);
            $unreadCount = $this->notificationService->getUnreadCountForUser($user);

            return response()->json([
                'success' => true,
                'data' => $paginator->items(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'unread_count' => $unreadCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách thông báo User ID ' . $user->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi truy xuất dữ liệu.',
            ], 500);
        }
    }

    /**
     * Đánh dấu một thông báo cụ thể là đã đọc.
     *
     * @param Request $request
     * @param int $id ID thông báo cần đọc
     * @return JsonResponse
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        try {
            $success = $this->notificationService->markAsRead($user, $id);
            $unreadCount = $this->notificationService->getUnreadCountForUser($user);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã đánh dấu đọc thông báo.',
                    'unread_count' => $unreadCount,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Thông báo không tồn tại hoặc không thuộc quyền sở hữu.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Lỗi khi đánh dấu đọc thông báo ID ' . $id . ' cho User ID ' . $user->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể thực hiện tác vụ này.',
            ], 500);
        }
    }

    /**
     * Đánh dấu tất cả thông báo hiện có là đã đọc.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        try {
            $this->notificationService->markAllAsRead($user);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu đọc tất cả thông báo.',
                'unread_count' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi đánh dấu đọc tất cả thông báo cho User ID ' . $user->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể thực hiện tác vụ này.',
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ thông báo thuộc tài khoản của người dùng (và ẩn thông báo chung).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearAll(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        try {
            $this->notificationService->clearAllNotifications($user);

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tất cả thông báo của bạn.',
                'unread_count' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa tất cả thông báo cho User ID ' . $user->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể thực hiện tác vụ này.',
            ], 500);
        }
    }
}
