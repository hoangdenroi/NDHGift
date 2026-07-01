<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GiftDurationPlan;
use Illuminate\Database\Seeder;

/**
 * Seeder cho bảng gói thời hạn quà tặng.
 *
 * 4 gói: 15 ngày (miễn phí), 30 ngày (5k), 90 ngày (10k), Vĩnh viễn (25k).
 */
class GiftDurationPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => '15d',
                'name' => '15 ngày',
                'duration_days' => 15,
                'price' => 0,
                'description' => 'Gói miễn phí — link quà tặng hoạt động trong 15 ngày.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => '30d',
                'name' => '30 ngày',
                'duration_days' => 30,
                'price' => 5000,
                'description' => 'Link quà tặng hoạt động trong 30 ngày.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => '90d',
                'name' => '90 ngày',
                'duration_days' => 90,
                'price' => 15000,
                'description' => 'Link quà tặng hoạt động trong 90 ngày.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'forever',
                'name' => 'Vĩnh viễn',
                'duration_days' => null,
                'price' => 35000,
                'description' => 'Link quà tặng hoạt động vĩnh viễn, không bao giờ hết hạn.',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            GiftDurationPlan::updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }
}
