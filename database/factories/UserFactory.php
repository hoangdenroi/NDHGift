<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Mật khẩu hiện tại được sử dụng bởi factory.
     */
    protected static ?string $password;

    /**
     * Định nghĩa trạng thái mặc định của model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'fullname' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'user',
            'status' => 'active',
            'balance' => 0,
            'is_deleted' => false,
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
        ];
    }

    /**
     * Trạng thái email chưa xác thực.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Trạng thái tài khoản Admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
