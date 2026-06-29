<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GiftCategory;
use App\Models\GiftTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GiftTemplate>
 */
class GiftTemplateFactory extends Factory
{
    protected $model = GiftTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => GiftCategory::factory(),
            'code' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10000, 100000),
            'discount' => $this->faker->numberBetween(0, 50),
            'sold' => $this->faker->numberBetween(0, 1000),
            'stars' => $this->faker->numberBetween(0, 5000),
            'is_hot' => $this->faker->boolean(20),
            'is_active' => true,
            'demo_url' => '#',
            'guide_url' => '#',
            'video_url' => '#',
            'meta_title' => $this->faker->sentence(5),
            'meta_description' => $this->faker->sentence(10),
            'meta_keywords' => implode(',', $this->faker->words(5)),
        ];
    }
}
