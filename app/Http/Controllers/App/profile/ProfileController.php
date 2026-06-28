<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ToggleAnonymousRequest;
use App\Services\UserLevelService;
use App\Models\XpTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ProfileController extends Controller
{
    /**
     * Khởi tạo controller — inject service qua constructor.
     */
    public function __construct(
        protected UserLevelService $userLevelService
    ) {}

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
        if (! $user) {
            return redirect()->route('login', ['locale' => app()->getLocale()]);
        }

        // Validate dữ liệu đầu vào nghiêm ngặt bảo vệ hệ thống
        $isEmailUpdate = $request->has('email') && ! $request->has('fullname');
        if ($isEmailUpdate) {
            $request->validate([
                'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            ]);
        } else {
            $request->validate([
                'fullname' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        }

        // Rate Limiter: Tối đa 5 lần thao tác cập nhật trong vòng 1 phút
        $rateLimitKey = 'update-profile:'.$user->id;
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
                    if ($rawAvatar && ! str_starts_with($rawAvatar, 'http')) {
                        $oldFilePath = public_path($rawAvatar);
                        if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                            @unlink($oldFilePath);
                        }
                    }

                    // Lưu file mới với tên ngẫu nhiên an toàn
                    $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                    $destinationPath = public_path('uploads/avatars');

                    // Tạo thư mục nếu chưa tồn tại
                    if (! file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    $file->move($destinationPath, $fileName);
                    $user->setAttribute('avatar_url', '/uploads/avatars/'.$fileName);
                }
            }

            $user->save();

            // Gửi toast/session thông báo thành công
            return back()->with('success', 'Cập nhật thông tin cá nhân thành công!');

        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật hồ sơ cá nhân User ID '.$user->id.': '.$e->getMessage());

            return back()->withErrors(['error' => 'Đã có lỗi xảy ra trong quá trình lưu trữ. Vui lòng thử lại sau.']);
        }
    }

    /**
     * Cập nhật cấu hình trải nghiệm người dùng (settings).
     */
    public function updateSettings(Request $request): JsonResponse
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
            if (! is_array($value)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu giao diện không hợp lệ.',
                ], 422);
            }
            $mode = $value['mode'] ?? 'auto';
            $primaryColor = $value['primaryColor'] ?? '#0d59f2';

            if (! in_array($mode, ['light', 'dark', 'auto'], true) || ! preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor)) {
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
            if (! is_array($value)) {
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
            if (! is_string($value) || ! in_array($value, ['vi', 'en'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ngôn ngữ chọn không hỗ trợ.',
                ], 422);
            }
            $validatedValue = strip_tags($value);
        }

        $user = $request->user();
        if (! $user) {
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
    public function xpTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
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

    /**
     * Lấy dữ liệu bảng xếp hạng XP và thứ hạng cá nhân.
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $leaderboardData = $this->userLevelService->getLeaderboard();
        $userRanking = $this->userLevelService->getUserRanking($user);

        // Đánh dấu dòng nào là user hiện tại trong top 10
        $top = array_map(function (array $entry) use ($user) {
            $entry['is_current_user'] = $entry['user_id'] === $user->id;

            return $entry;
        }, $leaderboardData['top']);

        // Lấy trạng thái ẩn danh hiện tại của user
        $userLevel = $user->userLevel;
        $metadata = $userLevel?->metadata ?? [];
        $isAnonymous = (bool) ($metadata['is_anonymous_leaderboard'] ?? false);

        return response()->json([
            'success' => true,
            'top' => $top,
            'total_ranked' => $leaderboardData['total_ranked'],
            'my_rank' => $userRanking['rank'],
            'my_top_percent' => $userRanking['top_percent'],
            'my_in_top_10' => $userRanking['in_top_10'],
            'is_anonymous' => $isAnonymous,
        ]);
    }

    /**
     * Toggle trạng thái ẩn danh trên bảng xếp hạng.
     */
    public function toggleAnonymous(ToggleAnonymousRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $newState = $this->userLevelService->toggleAnonymous($user);

            return response()->json([
                'success' => true,
                'is_anonymous' => $newState,
                'message' => $newState
                    ? 'Đã bật chế độ ẩn danh trên bảng xếp hạng.'
                    : 'Đã tắt chế độ ẩn danh trên bảng xếp hạng.',
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi toggle ẩn danh leaderboard User ID '.$user->id.': '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại sau.',
            ], 500);
        }
    }

    /**
     * Gửi email xác thực tài khoản.
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã được xác thực trước đó.',
            ], 400);
        }

        // Giới hạn tần suất gửi email xác thực: tối đa 1 lần mỗi phút
        $rateLimitKey = 'send-verification:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Vui lòng đợi {$seconds} giây trước khi gửi lại yêu cầu.",
            ], 429);
        }

        try {
            $user->sendEmailVerificationNotification();
            RateLimiter::hit($rateLimitKey, 60);

            return response()->json([
                'success' => true,
                'message' => 'Liên kết xác thực đã được gửi tới địa chỉ email của bạn.',
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi gửi email xác thực user ID '.$user->id.': '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi email xác thực, vui lòng thử lại sau.',
            ], 500);
        }
    }

    /**
     * Nhận thưởng XP cho nhiệm vụ một lần (register hoặc verify_email).
     */
    public function claimQuestXp(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $request->validate([
            'quest_key' => 'required|string|in:register,verify_email',
        ]);

        $questKey = $request->input('quest_key');

        // Rate limit nhận thưởng tránh click spam
        $rateLimitKey = 'claim-quest:' . $user->id . ':' . $questKey;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Thao tác quá nhanh, vui lòng thử lại sau.',
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 10);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($user, $questKey) {
                // 1. Kiểm tra xem đã nhận chưa
                $hasClaimed = XpTransaction::where('user_id', $user->id)
                    ->where('source', $questKey)
                    ->exists();

                if ($hasClaimed) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn đã nhận thưởng cho nhiệm vụ này rồi.',
                    ], 400);
                }

                // 2. Kiểm tra điều kiện nhiệm vụ
                $xpRules = config('levels.xp_rules', []);
                $amount = 0;
                $description = '';

                if ($questKey === 'register') {
                    $amount = (int) ($xpRules['register']['xp'] ?? 50);
                    $description = 'Nhận điểm chào mừng thành viên mới';
                } elseif ($questKey === 'verify_email') {
                    if (! $user->hasVerifiedEmail()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Bạn cần xác thực địa chỉ email trước khi nhận thưởng.',
                        ], 400);
                    }
                    $amount = (int) ($xpRules['verify_email']['xp'] ?? 30);
                    $description = 'Xác thực địa chỉ email thành công';
                }

                if ($amount <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nhiệm vụ không hợp lệ hoặc phần thưởng không khả dụng.',
                    ], 400);
                }

                // 3. Cộng XP và ghi giao dịch
                $this->userLevelService->awardXp($user, $questKey, $amount);

                // Reload lại thông tin user level mới
                $user->load('userLevel');
                $progress = $this->userLevelService->calculateProgress($user);
                $tierConfig = $this->userLevelService->getTierBenefits($user->current_tier);

                return response()->json([
                    'success' => true,
                    'message' => "Nhận thưởng thành công! +{$amount} XP",
                    'data' => [
                        'total_xp' => $user->current_xp,
                        'tier' => $user->current_tier,
                        'tier_label' => $tierConfig['label'] ?? '',
                        'tier_icon' => $tierConfig['icon'] ?? '',
                        'tier_color' => $tierConfig['color'] ?? '',
                        'progress' => $progress,
                    ],
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Lỗi claim quest {$questKey} user ID ".$user->id.": ".$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại sau.',
            ], 500);
        }
    }

    /**
     * Gửi mã OTP xác nhận đến địa chỉ email mới.
     */
    public function sendEmailChangeOtp(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $newEmail = $request->input('email');

        // Rate Limiter: Tối đa 1 lần gửi mã OTP đổi email mỗi phút
        $rateLimitKey = 'send-email-otp:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Vui lòng đợi {$seconds} giây trước khi yêu cầu mã mới.",
            ], 429);
        }

        try {
            // Sinh mã OTP 6 chữ số ngẫu nhiên
            $otp = strval(rand(100000, 999999));

            // Lưu OTP vào Cache với thời gian sống 5 phút
            \Illuminate\Support\Facades\Cache::put(
                'change-email-otp:' . $user->id,
                [
                    'email' => $newEmail,
                    'otp' => $otp,
                ],
                now()->addMinutes(5)
            );

            // Gửi email HTML chứa mã xác thực tới hòm thư mới
            \Illuminate\Support\Facades\Mail::to($newEmail)->send(new \App\Mail\ChangeEmailOtpMail($otp));

            RateLimiter::hit($rateLimitKey, 60);

            return response()->json([
                'success' => true,
                'message' => 'Mã xác thực OTP đã được gửi tới địa chỉ email mới của bạn.',
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi gửi email thay đổi OTP user ID '.$user->id.': '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi mã xác thực, vui lòng thử lại sau.',
            ], 500);
        }
    }

    /**
     * Xác nhận mã OTP và thực hiện thay đổi địa chỉ email.
     */
    public function confirmEmailChange(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'otp' => 'required|string|size:6',
        ]);

        $email = $request->input('email');
        $otp = $request->input('otp');

        // Rate Limiter chống brute force OTP
        $rateLimitKey = 'confirm-email-otp:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Thử lại quá nhiều lần, vui lòng đợi 1 phút.',
            ], 429);
        }

        $cachedData = \Illuminate\Support\Facades\Cache::get('change-email-otp:' . $user->id);

        if (! $cachedData) {
            RateLimiter::hit($rateLimitKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực không tồn tại hoặc đã hết hạn.',
            ], 400);
        }

        if ($cachedData['email'] !== $email || $cachedData['otp'] !== $otp) {
            RateLimiter::hit($rateLimitKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực không chính xác hoặc email không khớp.',
            ], 400);
        }

        try {
            // Cập nhật thông tin email của user và tự động đánh dấu đã xác thực email
            $user->email = $email;
            $user->email_verified_at = now();
            $user->save();

            // Xóa cache OTP
            \Illuminate\Support\Facades\Cache::forget('change-email-otp:' . $user->id);
            RateLimiter::clear($rateLimitKey);

            return response()->json([
                'success' => true,
                'message' => 'Đổi địa chỉ email thành công và đã được xác thực.',
                'email' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xác nhận thay đổi email user ID '.$user->id.': '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi cập nhật email mới, vui lòng thử lại sau.',
            ], 500);
        }
    }
}
