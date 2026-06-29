<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory cho Model Coupon — dùng trong tests, không chạy trên production.
 *
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('????##')),
            'type' => $this->faker->randomElement(['percent', 'fixed']),
            'value' => $this->faker->randomFloat(2, 1000, 100000),
            'max_discount' => $this->faker->optional()->randomFloat(2, 5000, 50000),
            'min_order' => $this->faker->randomFloat(2, 0, 50000),
            'max_uses' => $this->faker->optional()->numberBetween(10, 1000),
            'used_count' => 0,
            'starts_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+6 months'),
            'is_active' => true,
            'status' => $this->faker->randomElement(['public', 'private']),
        ];
    }
}
