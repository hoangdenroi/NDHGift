<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TopupService;
use Illuminate\Console\Command;

/**
 * Dọn dẹp giao dịch nạp tiền PENDING đã hết hạn.
 *
 * Chạy mỗi 1 giờ qua Laravel Scheduler.
 * Chuyển trạng thái từ PENDING → EXPIRED và broadcast event realtime.
 */
class ExpirePendingTopups extends Command
{
    protected $signature = 'topup:expire-pending';

    protected $description = 'Chuyển các giao dịch nạp tiền PENDING đã quá hạn sang trạng thái EXPIRED';

    public function handle(TopupService $topupService): int
    {
        $count = $topupService->expireOldTransactions();

        $this->info("Đã expire {$count} giao dịch PENDING quá hạn.");

        return self::SUCCESS;
    }
}
