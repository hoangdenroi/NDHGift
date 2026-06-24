<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Hiển thị giao diện đăng ký.
     */
    public function create(Request $request): View
    {
        if ($request->has('ref')) {
            session(['affiliate_ref' => $request->query('ref')]);
        }

        return view('components.pages.auth.register');
    }

    /**
     * Xử lý yêu cầu đăng ký tài khoản mới.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Tự động sinh username từ email nếu không có trong request (tương thích với form UI mới)
        $username = $request->input('username');
        if (empty($username)) {
            $username = strstr($request->email, '@', true);
            // Loại bỏ các ký tự không hợp lệ cho username (chỉ cho phép alpha_dash)
            $username = preg_replace('/[^a-zA-Z0-9_-]/', '', $username);
            
            // Xử lý trùng lặp username
            $baseUsername = $username;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
        }

        $user = User::create([
            'username' => $username,
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'last_login_at' => now(),
            'settings' => [
                'language' => 'vi',
                'theme' => [
                    'mode' => 'auto',
                    'primaryColor' => '#f97316',
                    'headerColor' => 'default',
                    'navbarColor' => 'default',
                    'footerColor' => 'default',
                ],
                'notifications' => [
                    'email' => false,
                    'push' => true,
                ],
            ],
        ]);

        // 1. Phân tích Affiliate (Kiểm tra xem có gian lận tự giới thiệu hay không)
        $isFraud = false;
        $referrer = null;
        $refCode = $request->input('ref') ?: $request->cookie('affiliate_ref') ?: session('affiliate_ref');
        
        if ($refCode) {
            $referrer = User::where('affiliate_code', $refCode)->first();
            if ($referrer) {
                if ($referrer->id === $user->id) {
                    $isFraud = true;
                    \Illuminate\Support\Facades\Log::warning("Phát hiện tự giới thiệu (Trùng ID): Người dùng ID {$user->id} tự đăng ký bằng link của chính mình.");
                } else {
                    $refTracker = $request->cookie('ref_tracker');
                    $currentIp = $request->ip();
                    $referrerMetadata = $referrer->metadata ?? [];
                    $referrerIps = $referrerMetadata['recent_ips'] ?? [];

                    $isIpMatch = $currentIp && in_array($currentIp, $referrerIps, true);
                    $isCookieMatch = ($refTracker === $referrer->affiliate_code);

                    if ($isCookieMatch || $isIpMatch) {
                        $isFraud = true;
                        \Illuminate\Support\Facades\Log::warning("Phát hiện tự giới thiệu gian lận: Người dùng ID {$user->id} đăng ký qua link của ID {$referrer->id}. Trùng Cookie: " . ($isCookieMatch ? 'Có' : 'Không') . " - Trùng IP: " . ($isIpMatch ? 'Có' : 'Không') . " (IP: {$currentIp})");
                    }
                }
            }
        }

        // 2. Cộng XP đăng ký tài khoản mới cho chính User mới
        try {
            $registerXp = $isFraud ? 0 : (int) config('levels.xp_rules.register.xp', 50);
            if ($registerXp > 0) {
                app(\App\Services\UserLevelService::class)->awardXp($user, 'register', $registerXp);
            } else if ($isFraud) {
                \Illuminate\Support\Facades\Log::info("Không cộng XP chào mừng cho tài khoản clone ID {$user->id} do tự giới thiệu gian lận.");
                
                // Ghi nhận lịch sử giao dịch 0 XP cho tài khoản clone để hiển thị minh bạch trên giao diện
                \App\Models\XpTransaction::create([
                    'user_id' => $user->id,
                    'amount' => 0,
                    'source' => 'register_fraud_blocked',
                    'description' => 'Không được cộng XP đăng ký chào mừng do tự giới thiệu gian lận',
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi cộng XP chào mừng đăng ký: ' . $e->getMessage());
        }

        // 3. Xử lý gán referred_by hoặc Phạt tài khoản chính
        if ($refCode && $referrer) {
            if ($isFraud) {
                // Phạt trừ 50 XP của tài khoản chính (referrer)
                try {
                    app(\App\Services\UserLevelService::class)->awardXp(
                        $referrer,
                        'referral_fraud_penalty',
                        -50
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Lỗi khi phạt trừ XP tài khoản chính ID {$referrer->id}: " . $e->getMessage());
                }
            } else {
                // Thỏa mãn điều kiện an toàn, thiết lập liên kết giới thiệu
                $user->update(['referred_by' => $referrer->id]);

                // Phát sự kiện giới thiệu thành công (để Listeners cộng XP/thưởng)
                event(new \App\Events\UserReferred($referrer, $user));
            }

            // Dọn dẹp session và cookie để tránh lặp lại
            session()->forget('affiliate_ref');
            cookie()->queue(cookie()->forget('affiliate_ref'));
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('app.home.index', absolute: false));
    }
}
