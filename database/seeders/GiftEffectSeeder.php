<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GiftEffect;
use Illuminate\Database\Seeder;

/**
 * Seeder cho các hiệu ứng premium (Gift Effects).
 */
class GiftEffectSeeder extends Seeder
{
    public function run(): void
    {
        $effects = [
            [
                'code' => 'snow_fall',
                'name' => 'Tuyết rơi lãng mạn ❄️',
                'description' => 'Hiệu ứng những bông tuyết rơi nhẹ nhàng, thích hợp cho dịp Noel hoặc mùa đông.',
                'type' => 'animation',
                'price' => 5000.00,
                'preview_url' => '/assets/effects/previews/snow.gif',
                'icon' => 'ac_unit',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'fireworks',
                'name' => 'Pháo hoa rực rỡ 🎆',
                'description' => 'Hiệu ứng pháo hoa nổ lung linh sắc màu trên bầu trời đêm.',
                'type' => 'animation',
                'price' => 10000.00,
                'preview_url' => '/assets/effects/previews/fireworks.gif',
                'icon' => 'celebration',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'confetti',
                'name' => 'Mưa giấy màu 🎉',
                'description' => 'Hiệu ứng những mảnh giấy màu sắc rơi xuống vui nhộn cho sinh nhật, kỷ niệm.',
                'type' => 'animation',
                'price' => 5000.00,
                'preview_url' => '/assets/effects/previews/confetti.gif',
                'icon' => 'party_mode',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'heart_rain',
                'name' => 'Cơn mưa trái tim ❤️',
                'description' => 'Hiệu ứng những trái tim đỏ thắm rơi đầy lãng mạn dành cho tình nhân.',
                'type' => 'animation',
                'price' => 8000.00,
                'preview_url' => '/assets/effects/previews/hearts.gif',
                'icon' => 'favorite',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($effects as $effect) {
            GiftEffect::updateOrCreate(
                ['code' => $effect['code']],
                $effect
            );
        }
    }
}
