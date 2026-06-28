<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\XpTransaction;
use Carbon\Carbon;
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
     * @param  User  $user  Người dùng được cộng XP
     * @param  string  $source  Nguồn XP (register, verify_email, topup, gift_create, referral_signup, referral_first_deposit, login_streak)
     * @param  int  $amount  Số XP được cộng
     * @param  Model|null  $reference  Đối tượng liên kết gây ra hành động cộng XP
     * @return int Tổng số XP sau khi cộng
     *
     * @throws Exception
     */
    public function awardXp(User $user, string $source, int $amount, ?Model $reference = null): int
    {
        if ($amount === 0) {
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

            if (! $userLevel) {
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

            $desc = $amount > 0 
                ? "Cộng {$amount} XP từ nguồn {$source}" 
                : "Trừ " . abs($amount) . " XP do " . ($source === 'referral_fraud_penalty' ? 'gian lận tự giới thiệu' : $source);

            // Ghi nhận lịch sử giao dịch XP
            XpTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'source' => $source,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => $desc,
            ]);

            $oldXp = $userLevel->total_xp;
            $newXp = max(0, $oldXp + $amount);
            $wasFrozen = $userLevel->is_frozen;

            // Cập nhật XP và thời gian tương tác mới nhất
            $userLevel->total_xp = $newXp;
            if ($amount > 0) {
                $userLevel->last_xp_earned_at = now();
            }

            // Nếu tài khoản đang bị đóng băng (Frozen), hành động nhận XP bất kỳ sẽ kích hoạt lại
            if ($wasFrozen && $amount > 0) {
                $userLevel->is_frozen = false;

                // Gửi thông báo kích hoạt lại thành công
                Notification::create([
                    'user_id' => $user->id,
                    'scope' => 'user',
                    'title' => 'Tài khoản đã được kích hoạt lại',
                    'message' => 'Chào mừng bạn đã hoạt động trở lại! Cấp bậc của bạn đã được mở băng và khôi phục các quyền lợi.',
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

            // Log audit cộng/trừ XP
            AuditLogService::log(
                $amount > 0 ? 'award_xp' : 'deduct_xp',
                $userLevel,
                ['total_xp' => $oldXp],
                ['total_xp' => $newXp, 'source' => $source],
                $user->id
            );

            // Kiểm tra và thăng cấp / hạ cấp
            if ($amount > 0) {
                $this->checkAndUpgradeTier($user, $userLevel);
            } else {
                $this->checkAndDowngradeTier($user, $userLevel);
            }

            return $newXp;
        });
    }

    /**
     * Kiểm tra và thăng cấp bậc cho người dùng dựa trên XP tích lũy.
     *
     * @param  User  $user  Người dùng cần kiểm tra
     * @param  UserLevel|null  $userLevel  Bản ghi UserLevel đã được lock (nếu có)
     */
    public function checkAndUpgradeTier(User $user, ?UserLevel $userLevel = null): void
    {
        $userLevel = $userLevel ?? UserLevel::where('user_id', $user->id)->first();
        if (! $userLevel) {
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
     * Kiểm tra và hạ cấp bậc cho người dùng nếu XP rớt xuống dưới mức tối thiểu của cấp hiện tại.
     *
     * @param  User  $user  Người dùng cần kiểm tra
     * @param  UserLevel|null  $userLevel  Bản ghi UserLevel đã được lock (nếu có)
     */
    public function checkAndDowngradeTier(User $user, ?UserLevel $userLevel = null): void
    {
        $userLevel = $userLevel ?? UserLevel::where('user_id', $user->id)->first();
        if (! $userLevel) {
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

        // So sánh để hạ cấp
        if ($eligibleTier !== $currentTier) {
            $tierOrder = array_keys($configuredTiers);
            $currentIndex = array_search($currentTier, $tierOrder);
            $eligibleIndex = array_search($eligibleTier, $tierOrder);

            if ($eligibleIndex < $currentIndex) {
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
                    'title' => 'Tài khoản của bạn đã bị hạ cấp bậc thành viên',
                    'message' => "Do bị trừ điểm kinh nghiệm, tài khoản của bạn đã bị hạ xuống hạng {$tierIcon} {$tierLabel}.",
                    'type' => 'warning',
                ]);

                // Log audit hạ cấp
                AuditLogService::log(
                    'downgrade_tier',
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
     */
    public function getTierBenefits(string $tier): array
    {
        return config("levels.tiers.{$tier}") ?? config('levels.tiers.bronze');
    }

    /**
     * Lấy phần trăm chiết khấu (giảm giá) của người dùng khi mua template premium.
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
     */
    public function getXpEarningStats(User $user): array
    {
        $xpRules = config('levels.xp_rules', []);

        // 1. gift_create: Lấy số lượng đã tạo hôm nay
        $todayGiftCreateCount = XpTransaction::where('user_id', $user->id)
            ->where('source', 'gift_create')
            ->whereDate('created_at', Carbon::today())
            ->count();

        // 2. referral_signup: Lấy số lượng đã giới thiệu trong tháng này
        $thisMonthReferralCount = XpTransaction::where('user_id', $user->id)
            ->where('source', 'referral_signup')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // 3. register: Đã nhận chưa?
        $hasRegisterXp = XpTransaction::where('user_id', $user->id)
            ->where('source', 'register')
            ->exists() ? 1 : 0;

        // 4. verify_email: Đã nhận chưa?
        $hasVerifyEmailXp = XpTransaction::where('user_id', $user->id)
            ->where('source', 'verify_email')
            ->exists() ? 1 : 0;

        // 5. daily_checkin: Lấy streak hiện tại và trạng thái hôm nay
        $userLevel = UserLevel::where('user_id', $user->id)->first();
        $hasCheckedInToday = false;
        $currentStreak = 0;
        if ($userLevel) {
            $currentStreak = $userLevel->checkin_streak;
            $hasCheckedInToday = $userLevel->last_checked_in_at
                && Carbon::parse($userLevel->last_checked_in_at)->isSameDay(Carbon::today());
        }

        return [
            [
                'key' => 'daily_checkin',
                'title' => 'Điểm danh hàng ngày',
                'description' => 'Điểm danh hàng ngày tích lũy chuỗi 7 ngày (Ngày thứ 7 nhận thêm 30 XP)',
                'xp' => '+10 XP',
                'completed' => $hasCheckedInToday ? 1 : 0,
                'limit' => 1,
                'streak' => $currentStreak,
                'type' => 'checkin',
            ],
            [
                'key' => 'topup',
                'title' => 'Nạp tiền tích lũy',
                'description' => 'Tích lũy XP từ nạp tiền (Cứ mỗi 1.000đ nạp nhận 1 XP)',
                'xp' => '1 XP / 1000 đ',
                'completed' => null,
                'limit' => null,
                'type' => 'unlimited',
            ],
            [
                'key' => 'gift_create',
                'title' => 'Tạo trang quà tặng',
                'description' => $xpRules['gift_create']['description'] ?? 'Tạo trang quà tặng mới',
                'xp' => '+'.($xpRules['gift_create']['xp'] ?? 20).' XP',
                'completed' => $todayGiftCreateCount,
                'limit' => $xpRules['gift_create']['daily_cap'] ?? 5,
                'type' => 'daily',
            ],
            [
                'key' => 'referral_signup',
                'title' => 'Giới thiệu thành viên mới',
                'description' => $xpRules['referral_signup']['description'] ?? 'Giới thiệu thành viên đăng ký thành công',
                'xp' => '+'.($xpRules['referral_signup']['xp_referrer'] ?? 100).' XP',
                'completed' => $thisMonthReferralCount,
                'limit' => $xpRules['referral_signup']['monthly_cap_referrer'] ?? 10,
                'type' => 'monthly',
            ],
            [
                'key' => 'referral_first_deposit',
                'title' => 'Giới thiệu nạp tiền lần đầu',
                'description' => $xpRules['referral_first_deposit']['description'] ?? 'F1 nạp tiền lần đầu thành công',
                'xp' => '+'.($xpRules['referral_first_deposit']['xp_referrer'] ?? 100).' XP',
                'completed' => null,
                'limit' => null,
                'type' => 'unlimited',
            ],
            [
                'key' => 'register',
                'title' => 'Đăng ký tài khoản',
                'description' => $xpRules['register']['description'] ?? 'Nhận điểm chào mừng thành viên mới',
                'xp' => '+'.($xpRules['register']['xp'] ?? 50).' XP',
                'completed' => $hasRegisterXp,
                'limit' => 1,
                'type' => 'once',
                'is_requirement_met' => true,
            ],
            [
                'key' => 'verify_email',
                'title' => 'Xác thực địa chỉ email',
                'description' => $xpRules['verify_email']['description'] ?? 'Xác thực địa chỉ email thành công',
                'xp' => '+'.($xpRules['verify_email']['xp'] ?? 30).' XP',
                'completed' => $hasVerifyEmailXp,
                'limit' => 1,
                'type' => 'once',
                'is_requirement_met' => $user->hasVerifiedEmail(),
            ],
        ];
    }

    /**
     * Lấy bảng xếp hạng top 10 user có XP cao nhất.
     * Cache 5 phút để giảm tải database.
     *
     * @return array{top: list<array>, total_ranked: int}
     */
    public function getLeaderboard(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('leaderboard:top10', now()->addMinutes(5), function () {
            $topUsers = UserLevel::query()
                ->join('users', 'user_levels.user_id', '=', 'users.id')
                ->where('users.status', 'active')
                ->where('users.is_deleted', false)
                ->where('user_levels.total_xp', '>', 0)
                ->orderByDesc('user_levels.total_xp')
                ->orderBy('user_levels.tier_achieved_at')
                ->limit(10)
                ->select([
                    'user_levels.user_id',
                    'user_levels.total_xp',
                    'user_levels.tier',
                    'user_levels.metadata',
                    'users.fullname',
                    'users.avatar_url',
                ])
                ->get();

            $configuredTiers = config('levels.tiers', []);
            $rank = 0;

            $top = $topUsers->map(function ($item) use ($configuredTiers, &$rank) {
                $rank++;
                $metadata = is_string($item->metadata) ? json_decode($item->metadata, true) : ($item->metadata ?? []);
                $isAnonymous = (bool) ($metadata['is_anonymous_leaderboard'] ?? false);
                $tierConfig = $configuredTiers[$item->tier] ?? $configuredTiers['bronze'];

                return [
                    'rank' => $rank,
                    'user_id' => $item->user_id,
                    'fullname' => $isAnonymous ? 'Thành viên ẩn danh' : ($item->fullname ?? 'Thành viên'),
                    'avatar_url' => $isAnonymous ? null : $item->avatar_url,
                    'total_xp' => $item->total_xp,
                    'tier' => $item->tier,
                    'tier_icon' => $tierConfig['icon'] ?? '🥉',
                    'tier_label' => $tierConfig['label'] ?? 'Bronze Member',
                    'tier_color' => $tierConfig['color'] ?? '#CD7F32',
                    'is_anonymous' => $isAnonymous,
                ];
            })->all();

            // Tổng số user có XP > 0 (dùng để tính top %)
            $totalRanked = UserLevel::query()
                ->join('users', 'user_levels.user_id', '=', 'users.id')
                ->where('users.status', 'active')
                ->where('users.is_deleted', false)
                ->where('user_levels.total_xp', '>', 0)
                ->count();

            return [
                'top' => $top,
                'total_ranked' => $totalRanked,
            ];
        });
    }

    /**
     * Lấy thứ hạng và phần trăm top của user hiện tại trên bảng xếp hạng.
     *
     * @return array{rank: int, total_ranked: int, top_percent: float, in_top_10: bool}
     */
    public function getUserRanking(User $user): array
    {
        $userLevel = UserLevel::where('user_id', $user->id)->first();
        if (! $userLevel || $userLevel->total_xp <= 0) {
            return [
                'rank' => 0,
                'total_ranked' => 0,
                'top_percent' => 100.0,
                'in_top_10' => false,
            ];
        }

        // Đếm số user đứng trước (XP cao hơn OR bằng XP nhưng đạt mốc trước)
        $higherCount = UserLevel::query()
            ->join('users', 'user_levels.user_id', '=', 'users.id')
            ->where('users.status', 'active')
            ->where('users.is_deleted', false)
            ->where(function ($query) use ($userLevel) {
                $query->where('user_levels.total_xp', '>', $userLevel->total_xp)
                    ->orWhere(function ($q) use ($userLevel) {
                        $q->where('user_levels.total_xp', '=', $userLevel->total_xp)
                          ->where('user_levels.tier_achieved_at', '<', $userLevel->tier_achieved_at);
                    });
            })
            ->count();

        $rank = $higherCount + 1;

        // Tổng user có XP > 0
        $totalRanked = UserLevel::query()
            ->join('users', 'user_levels.user_id', '=', 'users.id')
            ->where('users.status', 'active')
            ->where('users.is_deleted', false)
            ->where('user_levels.total_xp', '>', 0)
            ->count();

        $topPercent = $totalRanked > 0
            ? round(($rank / $totalRanked) * 100, 1)
            : 100.0;

        return [
            'rank' => $rank,
            'total_ranked' => $totalRanked,
            'top_percent' => $topPercent,
            'in_top_10' => $rank <= 10,
        ];
    }

    /**
     * Toggle trạng thái ẩn danh trên bảng xếp hạng.
     * Lưu vào cột metadata (JSON) của user_levels, xóa cache leaderboard để cập nhật ngay.
     *
     * @return bool Trạng thái ẩn danh mới sau khi toggle
     */
    public function toggleAnonymous(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $userLevel = UserLevel::where('user_id', $user->id)->lockForUpdate()->first();
            if (! $userLevel) {
                $userLevel = UserLevel::create([
                    'user_id' => $user->id,
                    'total_xp' => 0,
                    'tier' => 'bronze',
                    'is_frozen' => false,
                    'last_xp_earned_at' => now(),
                    'tier_achieved_at' => now(),
                ]);
            }

            $metadata = $userLevel->metadata ?? [];
            if (is_string($metadata)) {
                $metadata = json_decode($metadata, true) ?? [];
            }

            $currentState = (bool) ($metadata['is_anonymous_leaderboard'] ?? false);
            $metadata['is_anonymous_leaderboard'] = ! $currentState;

            $userLevel->metadata = $metadata;
            $userLevel->save();

            // Xóa cache leaderboard để phản ánh thay đổi ngay lập tức
            \Illuminate\Support\Facades\Cache::forget('leaderboard:top10');

            return ! $currentState;
        });
    }

    /**
     * Thực hiện điểm danh hàng ngày cho người dùng.
     *
     * @return array|null Trả về thông tin điểm danh hoặc null nếu hôm nay đã điểm danh
     */
    public function checkin(User $user): ?array
    {
        return DB::transaction(function () use ($user) {
            $userLevel = UserLevel::where('user_id', $user->id)->lockForUpdate()->first();
            if (! $userLevel) {
                $userLevel = UserLevel::create([
                    'user_id' => $user->id,
                    'total_xp' => 0,
                    'tier' => 'bronze',
                    'checkin_streak' => 0,
                    'last_checked_in_at' => null,
                    'is_frozen' => false,
                    'last_xp_earned_at' => now(),
                    'tier_achieved_at' => now(),
                ]);
            }

            $today = Carbon::today();

            // Nếu đã điểm danh hôm nay, bỏ qua
            if ($userLevel->last_checked_in_at && Carbon::parse($userLevel->last_checked_in_at)->isSameDay($today)) {
                return null;
            }

            $config = config('levels.xp_rules.daily_checkin', [
                'xp_daily' => 10,
                'xp_streak_bonus' => 30,
                'streak_days' => 7,
            ]);

            $streak = 1;
            if ($userLevel->last_checked_in_at) {
                $lastCheckin = Carbon::parse($userLevel->last_checked_in_at)->startOfDay();
                $yesterday = Carbon::yesterday()->startOfDay();

                if ($lastCheckin->equalTo($yesterday)) {
                    // Nếu điểm danh hôm qua, tiếp tục chuỗi streak
                    $streak = ($userLevel->checkin_streak % $config['streak_days']) + 1;
                }
            }

            $xpDaily = (int) ($config['xp_daily'] ?? 10);
            $xpBonus = (int) ($config['xp_streak_bonus'] ?? 30);

            $amount = $xpDaily;
            $isStreakCompleted = ($streak === (int) ($config['streak_days'] ?? 7));
            if ($isStreakCompleted) {
                $amount += $xpBonus;
            }

            // 1. Tạo lịch sử giao dịch XP
            XpTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'source' => 'daily_checkin',
                'description' => $isStreakCompleted
                    ? "Điểm danh ngày {$streak} (Cộng {$xpDaily} XP + thưởng chuỗi {$xpBonus} XP)"
                    : "Điểm danh hàng ngày (Ngày {$streak}/7 - Cộng {$xpDaily} XP)",
            ]);

            // 2. Cập nhật thông tin UserLevel
            $oldXp = $userLevel->total_xp;
            $newXp = $oldXp + $amount;
            $wasFrozen = $userLevel->is_frozen;

            $userLevel->total_xp = $newXp;
            $userLevel->checkin_streak = $streak;
            $userLevel->last_checked_in_at = now();
            $userLevel->last_xp_earned_at = now();

            if ($wasFrozen) {
                $userLevel->is_frozen = false;
                Notification::create([
                    'user_id' => $user->id,
                    'scope' => 'user',
                    'title' => 'Tài khoản đã được kích hoạt lại',
                    'message' => 'Chào mừng bạn đã hoạt động trở lại! Cấp bậc của bạn đã được mở băng và khôi phục các quyền lợi.',
                    'type' => 'success',
                ]);
            }

            $userLevel->save();

            // 3. Đánh giá thăng cấp
            $this->checkAndUpgradeTier($user, $userLevel);

            return [
                'streak' => $streak,
                'xp_awarded' => $amount,
                'bonus_awarded' => $isStreakCompleted ? $xpBonus : 0,
            ];
        });
    }
}
