<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserReferred;
use App\Models\XpTransaction;
use App\Services\UserLevelService;
use Illuminate\Support\Facades\Log;

/**
 * Listener AwardReferralXp
 *
 * Xử lý cộng XP thưởng cho người giới thiệu và người được giới thiệu khi đăng ký qua link Affiliate.
 * Chạy đồng bộ (sync) để phản hồi giao diện tức thì.
 */
class AwardReferralXp
{
    /**
     * Khởi tạo listener.
     */
    public function __construct(
        protected UserLevelService $userLevelService
    ) {}

    /**
     * Xử lý event.
     *
     * @param UserReferred $event
     * @return void
     */
    public function handle(UserReferred $event): void
    {
        $referrer = $event->referrer;
        $referee = $event->referee;

        try {
            $config = config('levels.xp_rules.referral_signup');
            $xpReferrer = $config['xp_referrer'] ?? 100;
            $xpReferee = $config['xp_referee'] ?? 50;
            $monthlyCap = $config['monthly_cap_referrer'] ?? 10;

            // 1. Cộng XP cho người được giới thiệu (Referee) - Không giới hạn
            if ($xpReferee > 0) {
                $this->userLevelService->awardXp(
                    $referee,
                    'referral_signup',
                    $xpReferee,
                    $referrer
                );
            }

            // 2. Cộng XP cho người giới thiệu (Referrer) - Giới hạn hàng tháng
            if ($xpReferrer > 0) {
                // Kiểm tra số lần giới thiệu nhận XP trong tháng hiện tại của referrer
                $currentMonthCount = XpTransaction::where('user_id', $referrer->id)
                    ->where('source', 'referral_signup')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();

                if ($currentMonthCount < $monthlyCap) {
                    $this->userLevelService->awardXp(
                        $referrer,
                        'referral_signup',
                        $xpReferrer,
                        $referee
                    );
                } else {
                    Log::info("Người dùng ID: {$referrer->id} đã đạt giới hạn nhận XP giới thiệu trong tháng này ({$monthlyCap} lượt).");
                }
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi khi cộng XP từ giới thiệu thành viên mới: ' . $e->getMessage(), [
                'referrer_id' => $referrer->id,
                'referee_id' => $referee->id,
            ]);
        }
    }
}
