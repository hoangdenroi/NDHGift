<?php

declare(strict_types=1);

/**
 * Cấu hình hệ thống User Level, XP Rules, Decay Time & Adsense Tiers cho NDHGift.
 */
return [
    // Định nghĩa các cấp bậc thành viên
    'tiers' => [
        'bronze' => [
            'min_xp' => 0,
            'discount' => 0,          // 0% giảm giá khi mua template premium
            'ad_percent' => 100,      // Hiển thị 100% quảng cáo
            'label' => 'Bronze Member',
            'icon' => '🥉',
            'color' => '#CD7F32',
        ],
        'silver' => [
            'min_xp' => 500,
            'discount' => 5,           // Giảm 5%
            'ad_percent' => 70,        // Giảm 30% quảng cáo (ẩn popup)
            'label' => 'Silver Member',
            'icon' => '🥈',
            'color' => '#C0C0C0',
        ],
        'gold' => [
            'min_xp' => 2000,
            'discount' => 10,          // Giảm 10%
            'ad_percent' => 40,        // Giảm 60% quảng cáo (chỉ hiển thị footer banner)
            'label' => 'Gold Member',
            'icon' => '🥇',
            'color' => '#FFD700',
        ],
        'platinum' => [
            'min_xp' => 5000,
            'discount' => 15,          // Giảm 15%
            'ad_percent' => 10,        // Ẩn hầu hết quảng cáo, chỉ còn banner rất nhỏ
            'label' => 'Platinum Member',
            'icon' => '💎',
            'color' => '#E5E4E2',
        ],
        'diamond' => [
            'min_xp' => 15000,
            'discount' => 20,          // Giảm 20%
            'ad_percent' => 0,          // Không hiển thị bất kỳ quảng cáo nào
            'label' => 'Diamond Member',
            'icon' => '👑',
            'color' => '#B9F2FF',
        ],
    ],

    // Quy tắc cộng điểm XP
    'xp_rules' => [
        'register' => [
            'xp' => 50,
            'description' => 'Quà tặng đăng ký tài khoản mới',
        ],
        'verify_email' => [
            'xp' => 30,
            'description' => 'Xác thực địa chỉ email thành công',
        ],
        'topup' => [
            'xp_per_thousand' => 1,    // 1 XP cho mỗi 1.000đ nạp tiền thành công
            'description' => 'Tích lũy XP từ nạp tiền',
        ],
        'gift_create' => [
            'xp' => 20,
            'daily_cap' => 5,          // Tối đa nhận XP từ 5 trang quà tặng mỗi ngày
            'description' => 'Tạo trang quà tặng mới',
        ],
        'referral_signup' => [
            'xp_referrer' => 100,      // Cộng cho người giới thiệu
            'xp_referee' => 50,        // Cộng cho người được giới thiệu
            'monthly_cap_referrer' => 10, // Tối đa giới thiệu 10 người nhận XP mỗi tháng
            'description' => 'Giới thiệu thành viên mới',
        ],
        'referral_first_deposit' => [
            'xp_referrer' => 100,      // Cộng thêm XP cho người giới thiệu khi F1 nạp tiền lần đầu
            'commission_percent' => 10, // Hoa hồng 10% giá trị nạp tiền lần đầu của F1 cộng vào balance
            'description' => 'Thành viên được giới thiệu thực hiện nạp tiền lần đầu',
        ],
        'daily_checkin' => [
            'xp_daily' => 10,
            'xp_streak_bonus' => 30,
            'streak_days' => 7,
            'description' => 'Điểm danh hàng ngày tích lũy chuỗi 7 ngày',
        ],
    ],

    // Thời gian không hoạt động dẫn đến giảm cấp/đóng băng (ngày)
    'decay_days' => 60,
];
