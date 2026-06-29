<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

/**
 * Service xử lý logic nghiệp vụ quản lý User phía Admin.
 *
 * Tách biệt hoàn toàn khỏi Controller — Controller chỉ gọi Service, không chứa business logic.
 */
class UserAdminService
{
    /**
     * Lấy danh sách người dùng có filter, search, phân trang.
     *
     * @param array<string, mixed> $filters Mảng filter: status, role, search
     * @param int $perPage Số bản ghi mỗi trang
     * @return LengthAwarePaginator
     */
    public function getFilteredUsers(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()
            ->with('userLevel')
            ->where('is_deleted', false);

        // Lọc theo trạng thái tài khoản
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Lọc theo vai trò
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Tìm kiếm theo tên, email, unitcode
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm): void {
                $q->where('fullname', 'like', $searchTerm)
                    ->orWhere('username', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('unitcode', 'like', $searchTerm);
            });
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    /**
     * Tổng hợp thống kê người dùng cho dashboard admin.
     *
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        return [
            'totalUsers' => User::where('is_deleted', false)->count(),
            'totalBalance' => (float) User::where('is_deleted', false)->sum('balance'),
            'adminCount' => User::where('is_deleted', false)->where('role', 'admin')->count(),
            'userCount' => User::where('is_deleted', false)->where('role', 'user')->count(),
            'suspendedCount' => User::where('is_deleted', false)->where('status', 'suspended')->count(),
        ];
    }

    /**
     * Tạo người dùng mới từ dữ liệu đã validate.
     *
     * @param array<string, mixed> $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        $user = new User();
        $user->fill($data);

        // Xử lý role riêng biệt — chống mass assignment
        if (isset($data['role'])) {
            $user->role = $data['role'];
        }

        $user->save();

        return $user;
    }

    /**
     * Cập nhật thông tin người dùng.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        // Chỉ hash password nếu được truyền vào
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->fill($data);

        // Xử lý role riêng biệt — chống mass assignment
        if (isset($data['role'])) {
            $user->role = $data['role'];
        }

        $user->save();

        return $user;
    }

    /**
     * Chuyển đổi trạng thái khóa/mở khóa tài khoản.
     *
     * @param User $user
     * @param string|null $reason Lý do khóa (chỉ khi khóa)
     * @return User
     */
    public function toggleStatus(User $user, ?string $reason = null): User
    {
        if ($user->status === 'active') {
            $user->status = 'suspended';
            $user->banned_reason = $reason;
            $user->suspended_at = now();
        } else {
            $user->status = 'active';
            $user->banned_reason = null;
            $user->suspended_at = null;
        }

        $user->save();

        return $user;
    }
}
