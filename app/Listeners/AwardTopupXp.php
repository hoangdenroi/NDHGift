<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserTopupSucceeded;
use App\Services\UserLevelService;
use Illuminate\Support\Facades\Log;

/**
 * Listener AwardTopupXp
 *
 * Tự động tính toán và cộng điểm kinh nghiệm (XP) cho người dùng khi nạp tiền thành công.
 * Chạy đồng bộ (sync) để đảm bảo tính nhất quán dữ liệu ngay lập tức.
 */
class AwardTopupXp
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
     * @param UserTopupSucceeded $event
     * @return void
     */
    public function handle(UserTopupSucceeded $event): void
    {
        $transaction = $event->transaction;
        $user = $event->user;

        // Chỉ xử lý giao dịch nạp tiền thành công và có số tiền lớn hơn 0
        if ($transaction->status !== 'SUCCESS' || $transaction->amount <= 0) {
            return;
        }

        try {
            $xpConfig = config('levels.xp_rules.topup');
            $xpPerThousand = $xpConfig['xp_per_thousand'] ?? 1;

            // Tính toán XP: 1 XP cho mỗi 1,000đ
            $xpAmount = (int) (floor($transaction->amount / 1000) * $xpPerThousand);

            if ($xpAmount > 0) {
                $this->userLevelService->awardXp(
                    $user,
                    'topup',
                    $xpAmount,
                    $transaction
                );
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi khi cộng XP từ giao dịch nạp tiền: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
            ]);
        }
    }
}
