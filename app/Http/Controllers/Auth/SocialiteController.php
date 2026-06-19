<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
     * Chuyển hướng người dùng sang trang OAuth của nhà cung cấp dịch vụ mạng xã hội (Google / Facebook).
     */
    public function redirect(Request $request, string $provider)
    {
        // Lưu trạng thái "ghi nhớ đăng nhập" vào session trước khi redirect
        $request->session()->put('socialite_remember', $request->boolean('remember'));

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->stateless()->redirect();
    }

    /**
     * Nhận phản hồi callback từ OAuth provider, xác thực hoặc tạo tài khoản người dùng tương ứng.
     */
    public function callback(string $provider)
    {
        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($provider);
            $socialUser = $driver->stateless()->user();

            // Sử dụng các getter chuẩn thay vì truy cập thuộc tính trực tiếp để tương thích PHP 8.2+
            $email = $socialUser->getEmail();
            $name = $socialUser->getName();
            $avatar = $socialUser->getAvatar();
            $socialId = $socialUser->getId();

            if (empty($email)) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Không thể lấy thông tin email từ tài khoản '.ucfirst($provider).' của bạn, vui lòng thử tài khoản khác hoặc cập nhật email trong cài đặt tài khoản '.ucfirst($provider).' của bạn.',
                ]);
            }

            // Tên cột lưu social id trong DB: google_id hoặc facebook_id
            $providerIdField = $provider.'_id';
            $user = User::where('email', $email)->first();

            if ($user) {
                // Đã có tài khoản với email này, cập nhật social_id và last_login_at
                $user->update([
                    $providerIdField => $socialId,
                    'last_login_at' => now(),
                    'avatar_url' => $user->avatar_url ?: $avatar, // Chỉ gán avatar MXH nếu chưa có avatar riêng
                ]);
            } else {
                // Chưa có tài khoản, tạo mới
                $user = User::create([
                    'username' => Str::slug($name, '_').'_'.Str::random(4),
                    'fullname' => $name,
                    'email' => $email,
                    $providerIdField => $socialId,
                    'role' => 'user',
                    'status' => 'active',
                    'last_login_at' => now(),
                    'avatar_url' => $avatar,
                    'password' => Hash::make(Str::password(16)),
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
            }

            // Đọc trạng thái "ghi nhớ đăng nhập" từ session
            $remember = session()->pull('socialite_remember', false);
            Auth::login($user, $remember);

            return redirect()->intended(url('/'));
        } catch (\Exception $e) {
            Log::error("Socialite Login Error ({$provider}): ".$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Đã có lỗi xảy ra khi đăng nhập bằng '.ucfirst($provider).': '.$e->getMessage(),
            ]);
        }
    }
}
