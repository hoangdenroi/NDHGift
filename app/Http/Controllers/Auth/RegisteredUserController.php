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

        // Cộng XP đăng ký tài khoản mới cho chính User mới
        try {
            $registerXp = (int) config('levels.xp_rules.register.xp', 50);
            if ($registerXp > 0) {
                app(\App\Services\UserLevelService::class)->awardXp($user, 'register', $registerXp);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi cộng XP chào mừng đăng ký: ' . $e->getMessage());
        }

        // Xử lý giới thiệu (Affiliate)
        $refCode = $request->input('ref') ?: session('affiliate_ref');
        if ($refCode) {
            $referrer = User::where('affiliate_code', $refCode)->first();
            if ($referrer && $referrer->id !== $user->id) {
                $user->update(['referred_by' => $referrer->id]);

                // Phát sự kiện giới thiệu thành công
                event(new \App\Events\UserReferred($referrer, $user));
            }
            session()->forget('affiliate_ref');
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('app.home.index', absolute: false));
    }
}
