<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\XpTransaction;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service UserLevelService
 *
 * Chứa logic nghiệp vụ liên quan đến cấp bậc, XP, thăng/giảm cấp và ưu đãi tương ứng.
 */
class UserLevelService
{
    /**
     * Cộng điểm kinh nghiệm (XP) cho người dùng.
     * Sử dụng DB Transaction và Pessimistic Locking để chống Race Condition.
     *
     * @param User $user Người dùng được cộng XP
     * @param string $source Nguồn XP (register, verify_email, topup, gift_create, referral_signup, referral_first_deposit, login_streak)
     * @param int $amount Số XP được cộng
     * @param Model|null $reference Đối tượng liên kết gây ra hành động cộng XP
     * @return int Tổng số XP sau khi cộng
     * @throws Exception
     */
    public function awardXp(User $user, string $source, int $amount, ?Model $reference = null): int
    {
        if ($amount <= 0) {
            return $user->current_xp;
        }

        return DB::transaction(function () use ($user, $source, $amount, $reference) {
            // 1. Tạo bản ghi XpTransaction trước để kích hoạt Unique Constraint (chống cộng lặp)
            $refType = $reference ? get_class($reference) : null;
            $refId = $reference ? $reference->getKey() : null;

            // Kiểm tra xem đã tồn tại giao dịch XP này chưa (nếu có reference)
            if ($refType && $refId) {
                $exists = XpTransaction::where('user_id', $user->id)
                    ->where('source', $source)
                    ->where('reference_type', $refType)
                    ->where('reference_id', $refId)
                    ->exists();

                if ($exists) {
                    // Nếu đã nhận rồi thì bỏ qua không ném lỗi để app chạy mượt mà, chỉ return XP hiện tại
                    return $user->current_xp;
                }
            }

            // Lock bản ghi UserLevel của User hiện tại để đảm bảo tính toàn vẹn dữ liệu
            $userLevel = UserLevel::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$userLevel) {
                // Tạo mới nếu chưa có
                $userLevel = UserLevel::create([
                    'user_id' => $user->id,
                    'total_xp' => 0,
                    'tier' => 'bronze',
                    'is_frozen' => false,
                    'last_xp_earned_at' => now(),
                    'tier_achieved_at' => now(),
                ]);
            }

            // Ghi nhận lịch sử giao dịch XP
            XpTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'source' => $source,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => "Cộng {$amount} XP từ nguồn {$source}",
            ]);

            $oldXp = $userLevel->total_xp;
            $newXp = $oldXp + $amount;
            $wasFrozen = $userLevel->is_frozen;

            // Cập nhật XP và thời gian tương tác mới nhất
            $userLevel->total_xp = $newXp;
            $userLevel->last_xp_earned_at = now();

            // Nếu tài khoản đang bị đóng băng (Frozen), hành động nhận XP bất kỳ sẽ kích hoạt lại
            if ($wasFrozen) {
                $userLevel->is_frozen = false;

                // Gửi thông báo kích hoạt lại thành công
                Notification::create([
                    'user_id' => $user->id,
                    'scope' => 'user',
                    'title' => 'Tài khoản đã được kích hoạt lại',
                    'message' => "Chào mừng bạn đã hoạt động trở lại! Cấp bậc của bạn đã được mở băng và khôi phục các quyền lợi.",
                    'type' => 'success',
                ]);

                // Log audit
                AuditLogService::log(
                    'reactivate_level',
                    $userLevel,
                    ['is_frozen' => true],
                    ['is_frozen' => false],
                    $user->id
                );
            }

            $userLevel->save();

            // Log audit cộng XP
            AuditLogService::log(
                'award_xp',
                $userLevel,
                ['total_xp' => $oldXp],
                ['total_xp' => $newXp, 'source' => $source],
                $user->id
            );

            // Kiểm tra và thăng cấp (nếu đủ điều kiện)
            $this->checkAndUpgradeTier($user, $userLevel);

            return $newXp;
        });
    }

    /**
     * Kiểm tra và thăng cấp bậc cho người dùng dựa trên XP tích lũy.
     *
     * @param User $user Người dùng cần kiểm tra
     * @param UserLevel|null $userLevel Bản ghi UserLevel đã được lock (nếu có)
     * @return void
     */
    public function checkAndUpgradeTier(User $user, ?UserLevel $userLevel = null): void
    {
        $userLevel = $userLevel ?? UserLevel::where('user_id', $user->id)->first();
        if (!$userLevel) {
            return;
        }

        $totalXp = $userLevel->total_xp;
        $currentTier = $userLevel->tier;
        $configuredTiers = config('levels.tiers', []);

        // Xác định tier cao nhất phù hợp với XP hiện tại
        $eligibleTier = 'bronze';
        foreach ($configuredTiers as $tierKey => $tierConfig) {
            if ($totalXp >= $tierConfig['min_xp']) {
                $eligibleTier = $tierKey;
            }
        }

        // So sánh để nâng cấp
        if ($eligibleTier !== $currentTier) {
            // Chỉ nâng cấp lên (không tự động hạ cấp ở đây vì hạ cấp xử lý qua Decay command riêng)
            $tierOrder = array_keys($configuredTiers);
            $currentIndex = array_search($currentTier, $tierOrder);
            $eligibleIndex = array_search($eligibleTier, $tierOrder);

            if ($eligibleIndex > $currentIndex) {
                $oldTier = $currentTier;
                $userLevel->tier = $eligibleTier;
                $userLevel->tier_achieved_at = now();
                $userLevel->save();

                $tierLabel = $configuredTiers[$eligibleTier]['label'] ?? $eligibleTier;
                $tierIcon = $configuredTiers[$eligibleTier]['icon'] ?? '';

                // Gửi thông báo hệ thống
                Notification::create([
                    'user_id' => $user->id,
                    'scope' => 'user',
                    'title' => 'Chúc mừng bạn đã thăng cấp thành viên!',
                    'message' => "Tài khoản của bạn đã được nâng cấp thành công lên hạng {$tierIcon} {$tierLabel} với nhiều ưu đãi mới.",
                    'type' => 'success',
                ]);

                // Log audit thăng cấp
                AuditLogService::log(
                    'upgrade_tier',
                    $userLevel,
                    ['tier' => $oldTier],
                    ['tier' => $eligibleTier],
                    $user->id
                );
            }
        }
    }

    /**
     * Lấy thông tin cấu hình chi tiết của một cấp bậc.
     *
     * @param string $tier
     * @return array
     */
    public function getTierBenefits(string $tier): array
    {
        return config("levels.tiers.{$tier}") ?? config('levels.tiers.bronze');
    }

    /**
     * Lấy phần trăm chiết khấu (giảm giá) của người dùng khi mua template premium.
     *
     * @param User $user
     * @return float
     */
    public function getDiscountForUser(User $user): float
    {
        // Nếu tài khoản bị đóng băng (Frozen), toàn bộ ưu đãi bị khóa (về 0)
        if ($user->is_tier_frozen) {
            return 0.0;
        }

        $tier = $user->current_tier;
        $tierConfig = $this->getTierBenefits($tier);

        return (float) ($tierConfig['discount'] ?? 0.0);
    }

    /**
     * Lấy mật độ quảng cáo Google AdSense của người dùng (0 - 100%).
     *
     * @param User $user
     * @return int
     */
    public function getAdPercentForUser(User $user): int
    {
        // Nếu tài khoản bị đóng băng (Frozen), hiển thị 100% quảng cáo
        if ($user->is_tier_frozen) {
            return 100;
        }

        $tier = $user->current_tier;
        $tierConfig = $this->getTierBenefits($tier);

        return (int) ($tierConfig['ad_percent'] ?? 100);
    }

    /**
     * Tính toán tiến trình thăng cấp (progress) để hiển thị lên giao diện UI.
     *
     * @param User $user
     * @return array{
     *     current_xp: int,
     *     next_tier_xp: int,
     *     next_tier_label: string,
     *     next_tier_icon: string,
     *     percent: int,
     *     is_max: bool
     * }
     */
    public function calculateProgress(User $user): array
    {
        $currentXp = $user->current_xp;
        $currentTier = $user->current_tier;
        $configuredTiers = config('levels.tiers', []);
        $tierOrder = array_keys($configuredTiers);
        
        $currentIndex = array_search($currentTier, $tierOrder);
        $nextIndex = $currentIndex !== false ? $currentIndex + 1 : false;

        // Nếu đã ở Diamond (tier cao nhất)
        if ($nextIndex === false || $nextIndex >= count($tierOrder)) {
            return [
                'current_xp' => $currentXp,
                'next_tier_xp' => $currentXp,
                'next_tier_label' => '',
                'next_tier_icon' => '',
                'percent' => 100,
                'is_max' => true,
            ];
        }

        $nextTierKey = $tierOrder[$nextIndex];
        $nextTierConfig = $configuredTiers[$nextTierKey];
        $currentTierConfig = $configuredTiers[$currentTier];

        $minXpOfCurrent = (int) ($currentTierConfig['min_xp'] ?? 0);
        $minXpOfNext = (int) ($nextTierConfig['min_xp'] ?? 0);

        $xpEarnedInCurrentRange = $currentXp - $minXpOfCurrent;
        $xpNeededForNextRange = $minXpOfNext - $minXpOfCurrent;

        $percent = $xpNeededForNextRange > 0 
            ? (int) min(100, max(0, round(($xpEarnedInCurrentRange / $xpNeededForNextRange) * 100))) 
            : 0;

        return [
            'current_xp' => $currentXp,
            'next_tier_xp' => $minXpOfNext,
            'next_tier_label' => $nextTierConfig['label'] ?? '',
            'next_tier_icon' => $nextTierConfig['icon'] ?? '',
            'percent' => $percent,
            'is_max' => false,
        ];
    }

    /**
     * Lấy thống kê các hoạt động tích lũy XP của người dùng bao gồm số lượt đã hoàn thành hôm nay/tháng này và giới hạn.
     *
     * @param User $user
     * @return array
     */
    public function getXpEarningStats(User $user): array
    {
        $xpRules = config('levels.xp_rules', []);

        // 1. gift_create: Lấy số lượng đã tạo hôm nay
        $todayGiftCreateCount = XpTransaction::where('user_id', $user->id)
            ->where('source', 'gift_create')
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->count();

        // 2. referral_signup: Lấy số lượng đã giới thiệu trong tháng này
        $thisMonthReferralCount = XpTransaction::where('user_id', $user->id)
            ->where('source', 'referral_signup')
            ->whereMonth('created_at', \Carbon\Carbon::now()->month)
            ->whereYear('created_at', \Carbon\Carbon::now()->year)
            ->count();

        // 3. register: Đã nhận chưa?
        $hasRegisterXp = XpTransaction::where('user_id', $user->id)
            ->where('source', 'register')
            ->exists() ? 1 : 0;

        // 4. verify_email: Đã nhận chưa?
        $hasVerifyEmailXp = XpTransaction::where('user_id', $user->id)
            ->where('source', 'verify_email')
            ->exists() ? 1 : 0;

        return [
            [
                'key' => 'topup',
                'title' => 'Nạp tiền tích lũy',
                'description' => $xpRules['topup']['description'] ?? 'Tích lũy XP từ nạp tiền',
                'xp' => '1 XP / 1.000đ nạp',
                'completed' => null,
                'limit' => null,
                'type' => 'unlimited'
            ],
            [
                'key' => 'gift_create',
                'title' => 'Tạo trang quà tặng',
                'description' => $xpRules['gift_create']['description'] ?? 'Tạo trang quà tặng mới',
                'xp' => '+' . ($xpRules['gift_create']['xp'] ?? 20) . ' XP',
                'completed' => $todayGiftCreateCount,
                'limit' => $xpRules['gift_create']['daily_cap'] ?? 5,
                'type' => 'daily'
            ],
            [
                'key' => 'referral_signup',
                'title' => 'Giới thiệu thành viên mới',
                'description' => $xpRules['referral_signup']['description'] ?? 'Giới thiệu thành viên đăng ký thành công',
                'xp' => '+' . ($xpRules['referral_signup']['xp_referrer'] ?? 100) . ' XP',
                'completed' => $thisMonthReferralCount,
                'limit' => $xpRules['referral_signup']['monthly_cap_referrer'] ?? 10,
                'type' => 'monthly'
            ],
            [
                'key' => 'referral_first_deposit',
                'title' => 'Giới thiệu nạp tiền lần đầu',
                'description' => $xpRules['referral_first_deposit']['description'] ?? 'F1 nạp tiền lần đầu thành công',
                'xp' => '+' . ($xpRules['referral_first_deposit']['xp_referrer'] ?? 100) . ' XP',
                'completed' => null,
                'limit' => null,
                'type' => 'unlimited'
            ],
            [
                'key' => 'register',
                'title' => 'Đăng ký tài khoản',
                'description' => $xpRules['register']['description'] ?? 'Nhận điểm chào mừng thành viên mới',
                'xp' => '+' . ($xpRules['register']['xp'] ?? 50) . ' XP',
                'completed' => $hasRegisterXp,
                'limit' => 1,
                'type' => 'once'
            ],
            [
                'key' => 'verify_email',
                'title' => 'Xác thực địa chỉ email',
                'description' => $xpRules['verify_email']['description'] ?? 'Xác thực địa chỉ email thành công',
                'xp' => '+' . ($xpRules['verify_email']['xp'] ?? 30) . ' XP',
                'completed' => $hasVerifyEmailXp,
                'limit' => 1,
                'type' => 'once'
            ],
        ];
    }
}
