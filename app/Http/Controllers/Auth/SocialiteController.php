<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SocialiteController extends Controller
{
    /**
     * Danh sách nhà cung cấp được hỗ trợ.
     */
    protected array $supportedProviders = ['google', 'facebook'];

    /**
     * Chuyển hướng người dùng sang trang OAuth của nhà cung cấp.
     */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->supportedProviders, true)) {
            abort(404);
        }

        // Lưu trạng thái "ghi nhớ đăng nhập" vào session trước khi redirect
        $request->session()->put('socialite_remember', $request->boolean('remember'));

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->stateless()->redirect();
    }

    /**
     * Nhận phản hồi callback từ OAuth provider.
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->supportedProviders, true)) {
            abort(404);
        }

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($provider);
            $socialUser = $driver->stateless()->user();

            $email = $socialUser->getEmail();
            $name = $socialUser->getName();
            $avatar = $socialUser->getAvatar();
            $socialId = $socialUser->getId();

            if (empty($email)) {
                return redirect()->route('login', ['locale' => app()->getLocale()])->withErrors([
                    'email' => 'Không thể lấy thông tin email từ tài khoản ' . ucfirst($provider) . ' của bạn.',
                ]);
            }

            // Tên cột lưu social id trong DB: google_id hoặc facebook_id
            $providerIdField = $provider . '_id';
            $user = User::where('email', $email)->first();

            if ($user) {
                // Đã có tài khoản với email này, cập nhật social_id và thông tin đăng nhập
                $updateData = [
                    $providerIdField => $socialId,
                    'last_login_at' => now(),
                ];

                // Nếu avatar mạng xã hội khác với avatar hiện tại thì cập nhật mới
                if (!empty($avatar) && $user->getRawOriginal('avatar_url') !== $avatar) {
                    $updateData['avatar_url'] = $avatar;
                }

                $user->update($updateData);
            } else {
                // Chưa có tài khoản, tạo mới
                // 1. Sinh username từ email
                $username = strstr($email, '@', true) ?: 'user';
                $username = preg_replace('/[^a-zA-Z0-9_-]/', '', $username);

                // Tránh trùng lặp username
                $baseUsername = $username;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $user = new User([
                    'username' => $username,
                    'fullname' => $name ?: $username,
                    'email' => $email,
                    $providerIdField => $socialId,
                    'avatar_url' => $avatar,
                    'password' => Hash::make(Str::password(16)),
                    'settings' => [
                        'language' => app()->getLocale(),
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

                // Các trường nhạy cảm được gán trực tiếp để tránh Mass Assignment Protection
                $user->role = 'user';
                $user->status = 'active';
                $user->email_verified_at = now();
                $user->last_login_at = now();
                $user->save();

                // 2. Xử lý Affiliate & cộng XP khi đăng ký mới bằng MXH
                $this->handleSocialRegistrationAffiliate($request, $user);
            }

            // Đọc trạng thái "ghi nhớ đăng nhập" từ session
            $remember = session()->pull('socialite_remember', false);
            Auth::login($user, $remember);

            // Chuyển hướng về trang chủ
            return redirect()->intended(route('app.home.index', ['locale' => app()->getLocale()]));
        } catch (\Exception $e) {
            Log::error("Socialite Login Error ({$provider}): " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login', ['locale' => app()->getLocale()])->withErrors([
                'email' => 'Đã có lỗi xảy ra khi đăng nhập bằng ' . ucfirst($provider) . '. Vui lòng thử lại sau.',
            ]);
        }
    }

    /**
     * Xử lý affiliate, XP và phát hiện gian lận khi đăng ký mới bằng tài khoản mạng xã hội.
     */
    private function handleSocialRegistrationAffiliate(Request $request, User $user): void
    {
        $isFraud = false;
        $referrer = null;
        $refCode = $request->cookie('affiliate_ref') ?: session('affiliate_ref');

        if ($refCode) {
            $referrer = User::where('affiliate_code', $refCode)->first();
            if ($referrer) {
                if ($referrer->id === $user->id) {
                    $isFraud = true;
                    Log::warning("Phát hiện tự giới thiệu qua Social (Trùng ID): Người dùng ID {$user->id} tự đăng ký bằng link của chính mình.");
                } else {
                    $refTracker = $request->cookie('ref_tracker');
                    $currentIp = $request->ip();
                    $referrerMetadata = $referrer->metadata ?? [];
                    $referrerIps = $referrerMetadata['recent_ips'] ?? [];

                    $isIpMatch = $currentIp && in_array($currentIp, $referrerIps, true);
                    $isCookieMatch = ($refTracker === $referrer->affiliate_code);

                    if ($isCookieMatch || $isIpMatch) {
                        $isFraud = true;
                        Log::warning("Phát hiện tự giới thiệu gian lận qua Social: Người dùng ID {$user->id} đăng ký qua link của ID {$referrer->id}. Trùng Cookie: " . ($isCookieMatch ? 'Có' : 'Không') . " - Trùng IP: " . ($isIpMatch ? 'Có' : 'Không') . " (IP: {$currentIp})");
                    }
                }
            }
        }

        // Cộng XP đăng ký tài khoản mới cho chính User mới
        try {
            $registerXp = $isFraud ? 0 : (int) config('levels.xp_rules.register.xp', 50);
            if ($registerXp > 0) {
                app(\App\Services\UserLevelService::class)->awardXp($user, 'register', $registerXp);
            } else if ($isFraud) {
                Log::info("Không cộng XP chào mừng cho tài khoản MXH clone ID {$user->id} do tự giới thiệu gian lận.");

                // Ghi nhận lịch sử giao dịch 0 XP cho tài khoản clone
                \App\Models\XpTransaction::create([
                    'user_id' => $user->id,
                    'amount' => 0,
                    'source' => 'register_fraud_blocked',
                    'description' => 'Không được cộng XP đăng ký chào mừng do tự giới thiệu gian lận',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi khi cộng XP chào mừng đăng ký MXH: ' . $e->getMessage());
        }

        // Xử lý gán referred_by hoặc phạt tài khoản chính
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
                    Log::error("Lỗi khi phạt trừ XP tài khoản chính ID {$referrer->id} qua Social: " . $e->getMessage());
                }
            } else {
                // Thỏa mãn điều kiện an toàn, thiết lập liên kết giới thiệu
                $user->update(['referred_by' => $referrer->id]);

                // Phát sự kiện giới thiệu thành công
                event(new \App\Events\UserReferred($referrer, $user));
            }

            // Dọn dẹp session và cookie
            session()->forget('affiliate_ref');
            cookie()->queue(cookie()->forget('affiliate_ref'));
        }
    }
}
