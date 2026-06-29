<?php

namespace Database\Factories;

use App\Models\GiftCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GiftCategory>
 */
class GiftCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'name' => ucwords($name),
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->randomElement(['cake', 'favorite', 'celebration', 'ac_unit', 'volunteer_activism']),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
