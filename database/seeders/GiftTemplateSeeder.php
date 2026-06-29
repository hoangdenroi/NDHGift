<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GiftCategory;
use App\Models\GiftTemplate;
use Illuminate\Database\Seeder;

/**
 * Seeder cho các mẫu quà tặng 3D.
 */
class GiftTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh mục tương ứng
        $loveCategory = GiftCategory::where('slug', 'love')->first();
        $birthdayCategory = GiftCategory::where('slug', 'birthday')->first();
        $christmasCategory = GiftCategory::where('slug', 'christmas')->first();

        // 1. Tạo mẫu Trái tim 3D (Love Category)
        if ($loveCategory) {
            GiftTemplate::updateOrCreate(
                ['code' => 'heart_3d'],
                [
                    'category_id' => $loveCategory->id,
                    'name' => 'Web Trái Tim 3D – Gửi Yêu Thương Bay Lên 💖',
                    'description' => 'Mẫu không gian 3D tương tác với hàng ngàn hạt particles tạo hình trái tim bay bổng, kèm theo vòng xoay văn bản chứa lời chúc yêu thương lấp lánh.',
                    'price' => 39999.00,
                    'discount' => 40,
                    'sold' => 389,
                    'stars' => 35000,
                    'is_hot' => true,
                    'is_active' => true,
                    'demo_url' => '/templates/heart_3d/index.html',
                    'meta_title' => 'Web Trái Tim 3D Tương Tác Cực Đẹp',
                    'meta_description' => 'Mẫu quà tặng tình yêu lãng mạn với hiệu ứng hạt particles trái tim 3D chuyển động mượt mà và vòng chữ tùy biến lời chúc.',
                    'meta_keywords' => 'trái tim 3d, threejs heart, web 3d love, quà tặng người yêu',
                ]
            );
        }

        // 2. Tạo mẫu Bánh sinh nhật 3D (Birthday Category)
        if ($birthdayCategory) {
            GiftTemplate::updateOrCreate(
                ['code' => 'birthday_cake_3d'],
                [
                    'category_id' => $birthdayCategory->id,
                    'name' => 'Birthday Special - Bánh sinh nhật 3D thổi nến cắt bánh 🎂',
                    'description' => 'Không gian sinh nhật ấm cúng với mô hình bánh sinh nhật 3D, hỗ trợ tương tác thổi nến ảo và cắt bánh độc đáo kèm nền nhạc chúc mừng sinh nhật.',
                    'price' => 49999.00,
                    'discount' => 30,
                    'sold' => 245,
                    'stars' => 12500,
                    'is_hot' => true,
                    'is_active' => true,
                    'demo_url' => '#',
                    'meta_title' => 'Bánh Sinh Nhật 3D Thổi Nến Ảo',
                    'meta_description' => 'Mẫu thiệp quà tặng sinh nhật 3D tương tác thổi nến và cắt bánh ngọt ngào gửi tặng bạn bè.',
                    'meta_keywords' => 'bánh sinh nhật 3d, thổi nến ảo, quà tặng sinh nhật, thiệp sinh nhật 3d',
                ]
            );
        }

        // 3. Tạo mẫu Giáng Sinh 3D (Christmas Category)
        if ($christmasCategory) {
            GiftTemplate::updateOrCreate(
                ['code' => 'christmas_snow_3d'],
                [
                    'category_id' => $christmasCategory->id,
                    'name' => 'Giáng Sinh Ấm Áp 3D - Tuyết Rơi Lung Linh ❄️',
                    'description' => 'Ngôi nhà gỗ Bắc Âu lung linh tuyết rơi, ông già Noel cưỡi tuần lộc bay qua bầu trời đêm 3D kỳ ảo và giai điệu Giáng sinh du dương.',
                    'price' => 59999.00,
                    'discount' => 33,
                    'sold' => 312,
                    'stars' => 3100,
                    'is_hot' => false,
                    'is_active' => true,
                    'demo_url' => '#',
                    'meta_title' => 'Quà Tặng Giáng Sinh 3D Kỳ Ảo',
                    'meta_description' => 'Trải nghiệm không gian đêm Noel ấm áp với tuyết rơi thực tế ảo mượt mà.',
                    'meta_keywords' => 'quà giáng sinh 3d, thiệp noel 3d, tuyết rơi 3d, threejs christmas',
                ]
            );
        }
    }
}
