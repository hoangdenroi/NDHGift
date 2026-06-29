<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder chính cho cơ sở dữ liệu hệ thống NDHGift.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Chạy các seeder để khởi tạo dữ liệu mẫu.
     */
    public function run(): void
    {
        // Cấu hình settings mặc định
        $defaultSettings = [
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
        ];

        // 1. Tạo tài khoản Admin hệ thống
        $admin = User::firstOrCreate(
            ['email' => 'admin@ndhgift.com'],
            [
                'username' => 'admin',
                'fullname' => 'Administrator',
                'password' => Hash::make('123456'),
                'phone' => '0999999999',
                'email_verified_at' => now(),
                'status' => 'active',
                'balance' => 10000000.00, // Ví dụ khởi tạo số dư mặc định 10 triệu
                'settings' => $defaultSettings,
            ]
        );

        // Gán quyền admin (bypass mass assignment vì cột role không nằm trong fillable)
        $admin->role = 'admin';
        $admin->save();

        // 2. Tạo một tài khoản User mẫu để phát triển/thử nghiệm
        $user = User::firstOrCreate(
            ['email' => 'user@ndhgift.com'],
            [
                'username' => 'demo_user',
                'fullname' => 'Demo User',
                'password' => Hash::make('123456'),
                'phone' => '0988888888',
                'email_verified_at' => now(),
                'status' => 'active',
                'balance' => 100000.00, // Số dư ban đầu 100k
                'settings' => $defaultSettings,
            ]
        );
        $user->role = 'user';
        $user->save();

        // 3. Tạo thêm 10 users ngẫu nhiên phục vụ test phân trang/giao diện
        User::factory(10)->create();

        // 4. Tạo các danh mục quà tặng mẫu
        $this->call(GiftCategorySeeder::class);
    }
}
