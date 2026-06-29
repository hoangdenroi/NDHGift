<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\UserAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

/**
 * Controller quản lý người dùng phía Admin.
 *
 * Skinny Controller — mọi business logic nằm trong UserAdminService.
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserAdminService $userService
    ) {}

    /**
     * Hiển thị danh sách người dùng với filter, search và thống kê.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'role', 'search']);
        $users = $this->userService->getFilteredUsers($filters);
        $stats = $this->userService->getStats();

        return view('components.pages.admin.users.user-index', compact('users', 'stats'));
    }

    /**
     * Tạo người dùng mới.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('toast_type', 'success')
            ->with('toast_message', 'Tạo người dùng thành công.');
    }

    /**
     * Cập nhật thông tin người dùng.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser($user, $request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('toast_type', 'success')
            ->with('toast_message', 'Cập nhật người dùng thành công.');
    }

    /**
     * Xóa mềm người dùng.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->softDelete();

        return redirect()
            ->route('admin.users.index')
            ->with('toast_type', 'success')
            ->with('toast_message', 'Đã xóa người dùng.');
    }

    /**
     * Khóa/mở khóa tài khoản người dùng.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $reason = strip_tags((string) $request->input('banned_reason', ''));

        $this->userService->toggleStatus($user, $reason ?: null);

        $message = $user->status === 'suspended'
            ? "Đã khóa tài khoản {$user->fullname}."
            : "Đã mở khóa tài khoản {$user->fullname}.";

        return redirect()
            ->route('admin.users.index')
            ->with('toast_type', 'success')
            ->with('toast_message', $message);
    }
}
