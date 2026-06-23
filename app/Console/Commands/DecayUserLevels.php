<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\UserLevel;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Command DecayUserLevels
 *
 * Tự động chạy định kỳ để hạ cấp bậc hoặc đóng băng quyền lợi thành viên
 * nếu họ không hoạt động (không nhận XP) trong 60 ngày liên tục.
 */
class DecayUserLevels extends Command
{
    /**
     * Tên và chữ ký đăng ký cho Artisan command.
     *
     * @var string
     */
    protected $signature = 'app:decay-user-levels';

    /**
     * Mô tả của command.
     *
     * @var string
     */
    protected $description = 'Kiểm tra và đóng băng/giảm cấp độ thành viên nếu không hoạt động trong 60 ngày';

    /**
     * Thực thi command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Bắt đầu kiểm tra giảm cấp độ thành viên do không hoạt động...');

        $decayDays = config('levels.decay_days', 60);
        $thresholdDate = Carbon::now()->subDays($decayDays);

        // Lấy tất cả user_levels chưa bị đóng băng và có thời gian nhận XP cuối cùng vượt quá 60 ngày
        $inactiveUserLevels = UserLevel::where('is_frozen', false)
            ->where('last_xp_earned_at', '<', $thresholdDate)
            ->get();

        $count = $inactiveUserLevels->count();
        $this->info("Tìm thấy {$count} tài khoản không hoạt động > {$decayDays} ngày.");

        if ($count === 0) {
            $this->info('Không có tài khoản nào cần xử lý.');
            return Command::SUCCESS;
        }

        $configuredTiers = config('levels.tiers', []);
        $tierOrder = array_keys($configuredTiers);

        foreach ($inactiveUserLevels as $userLevel) {
            $user = $userLevel->user;
            if (!$user) {
                continue;
            }

            DB::transaction(function () use ($userLevel, $user, $tierOrder, $configuredTiers) {
                // Lock dòng để tránh tranh chấp dữ liệu
                $lockedUserLevel = UserLevel::where('id', $userLevel->id)->lockForUpdate()->first();
                if (!$lockedUserLevel || $lockedUserLevel->is_frozen) {
                    return;
                }

                $oldTier = $lockedUserLevel->tier;
                $newTier = $oldTier;

                // Cơ chế giảm 1 cấp (nếu không phải là Bronze)
                if ($oldTier !== 'bronze') {
                    $currentIndex = array_search($oldTier, $tierOrder);
                    if ($currentIndex !== false && $currentIndex > 0) {
                        $newTier = $tierOrder[$currentIndex - 1];
                    }
                }

                // Thiết lập đóng băng (is_frozen) và lưu cấp mới
                $lockedUserLevel->is_frozen = true;
                $lockedUserLevel->tier = $newTier;
                $lockedUserLevel->tier_achieved_at = now();
                $lockedUserLevel->save();

                $tierLabel = $configuredTiers[$newTier]['label'] ?? $newTier;
                $tierIcon = $configuredTiers[$newTier]['icon'] ?? '';

                // Gửi thông báo đến user
                Notification::create([
                    'user_id' => $user->id,
                    'scope' => 'user',
                    'title' => 'Tài khoản thành viên bị đóng băng',
                    'message' => "Bạn đã không hoạt động trong 60 ngày. Tài khoản của bạn đã bị đóng băng và hạ cấp xuống {$tierIcon} {$tierLabel}. Hãy nạp tiền hoặc hoàn thành nhiệm vụ bất kỳ để kích hoạt lại quyền lợi.",
                    'type' => 'warning',
                ]);

                // Log audit hệ thống
                AuditLogService::log(
                    'decay_level',
                    $lockedUserLevel,
                    ['tier' => $oldTier, 'is_frozen' => false],
                    ['tier' => $newTier, 'is_frozen' => true],
                    $user->id
                );
            });

            $this->line("Đã đóng băng tài khoản User ID: {$user->id} (Hạ cấp: {$userLevel->tier} -> {$userLevel->tier})");
        }

        $this->info('Hoàn thành xử lý giảm cấp độ thành viên.');

        return Command::SUCCESS;
    }
}
