<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang Hồ sơ cá nhân.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        return view('components.pages.app.profile.profile-index', compact('user'));
    }

    /**
     * Cập nhật thông tin hồ sơ cá nhân và xử lý tải lên ảnh đại diện cục bộ.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login', ['locale' => app()->getLocale()]);
        }

        // Validate dữ liệu đầu vào nghiêm ngặt bảo vệ hệ thống
        $isEmailUpdate = $request->has('email') && !$request->has('fullname');
        if ($isEmailUpdate) {
            $request->validate([
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ]);
        } else {
            $request->validate([
                'fullname' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        }

        // Rate Limiter: Tối đa 5 lần thao tác cập nhật trong vòng 1 phút
        $rateLimitKey = 'update-profile:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors(['rate_limit' => "Bạn thao tác quá nhanh. Vui lòng thử lại sau {$seconds} giây."]);
        }
        RateLimiter::hit($rateLimitKey, 60);

        try {
            if ($isEmailUpdate) {
                $user->email = strip_tags($request->email);
                $user->save();
                return back()->with('success', 'Cập nhật email đăng nhập thành công!');
            }

            // Cập nhật thông tin cơ bản
            $user->fullname = strip_tags($request->fullname); // Sanitize chống XSS
            $user->phone = $request->phone ? strip_tags($request->phone) : null;

            // Xử lý upload avatar cục bộ
            if ($request->hasFile('avatar_file')) {
                $file = $request->file('avatar_file');

                // Validate thêm tính hợp lệ của file upload thực tế
                if ($file->isValid()) {
                    // Xóa file ảnh cũ nếu tồn tại
                    $rawAvatar = $user->getRawOriginal('avatar_url');
                    if ($rawAvatar && !str_starts_with($rawAvatar, 'http')) {
                        $oldFilePath = public_path($rawAvatar);
                        if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                            @unlink($oldFilePath);
                        }
                    }

                    // Lưu file mới với tên ngẫu nhiên an toàn
                    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $destinationPath = public_path('uploads/avatars');
                    
                    // Tạo thư mục nếu chưa tồn tại
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    $file->move($destinationPath, $fileName);
                    $user->setAttribute('avatar_url', '/uploads/avatars/' . $fileName);
                }
            }

            $user->save();

            // Gửi toast/session thông báo thành công
            return back()->with('success', 'Cập nhật thông tin cá nhân thành công!');

        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật hồ sơ cá nhân User ID ' . $user->id . ': ' . $e->getMessage());
            return back()->withErrors(['error' => 'Đã có lỗi xảy ra trong quá trình lưu trữ. Vui lòng thử lại sau.']);
        }
    }

    /**
     * Cập nhật cấu hình trải nghiệm người dùng (settings).
     */
    public function updateSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'key' => 'required|string|in:theme,notifications,language',
            'value' => 'required',
        ]);

        $key = $request->input('key');
        $value = $request->input('value');
        $validatedValue = null;

        // Validate sâu giá trị theo từng key để đảm bảo an toàn dữ liệu
        if ($key === 'theme') {
            if (!is_array($value)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu giao diện không hợp lệ.',
                ], 422);
            }
            $mode = $value['mode'] ?? 'auto';
            $primaryColor = $value['primaryColor'] ?? '#0d59f2';

            if (!in_array($mode, ['light', 'dark', 'auto'], true) || !preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cấu hình giao diện hoặc màu sắc không đúng định dạng.',
                ], 422);
            }
            $validatedValue = [
                'mode' => $mode,
                'primaryColor' => $primaryColor,
            ];
        } elseif ($key === 'notifications') {
            if (!is_array($value)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu cấu hình nhận thông báo không hợp lệ.',
                ], 422);
            }
            $validatedValue = [
                'email' => filter_var($value['email'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'push' => filter_var($value['push'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        } elseif ($key === 'language') {
            if (!is_string($value) || !in_array($value, ['vi', 'en'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ngôn ngữ chọn không hỗ trợ.',
                ], 422);
            }
            $validatedValue = strip_tags($value);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $settings = $user->settings ?? [];
        $settings[$key] = $validatedValue;

        $user->settings = $settings;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật cấu hình thành công!',
        ]);
    }

    /**
     * Lấy danh sách giao dịch điểm kinh nghiệm (XP) phân trang.
     */
    public function xpTransactions(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $transactions = $user->xpTransactions()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(5); // Phân trang 5 dòng mỗi trang

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
            'total' => $transactions->total(),
            'per_page' => $transactions->perPage(),
        ]);
    }
}
