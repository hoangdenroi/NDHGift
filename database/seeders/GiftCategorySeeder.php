<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GiftCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Birthday',
                'slug' => 'birthday',
                'description' => 'Mẫu quà tặng kỷ niệm sinh nhật, thổi nến cắt bánh ngọt ngào.',
                'icon' => 'cake',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Love',
                'slug' => 'love',
                'description' => 'Lời nhắn gửi lãng mạn, các mẫu 3D tình yêu ngọt ngào dành cho các cặp đôi.',
                'icon' => 'favorite',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Thank You',
                'slug' => 'thank',
                'description' => 'Bày tỏ lòng biết ơn, tri ân sâu sắc đến những người trân quý.',
                'icon' => 'volunteer_activism',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Anniversary',
                'slug' => 'anniversary',
                'description' => 'Kỷ niệm ngày cưới, ngày chung đôi và những chặng đường hạnh phúc bên nhau.',
                'icon' => 'celebration',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Christmas',
                'slug' => 'christmas',
                'description' => 'Món quà giáng sinh ấm áp, tuyết rơi lung linh và lời chúc an lành.',
                'icon' => 'ac_unit',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Tết',
                'slug' => 'tet',
                'description' => 'Chúc mừng năm mới, lộc xuân phơi phới, vạn sự như ý.',
                'icon' => 'ac_unit',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Tết Trung Thu',
                'slug' => 'mid_autumn',
                'description' => 'Trung thu đoàn viên, rước đèn phá cỗ dưới ánh trăng vàng.',
                'icon' => 'ac_unit',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Lễ Tình Nhân',
                'slug' => 'valentine',
                'description' => 'Valentine đỏ, tình cảm đong đầy, chocolate ngọt ngào.',
                'icon' => 'ac_unit',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            \App\Models\GiftCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
