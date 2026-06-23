<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserTopupSucceeded;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\UserLevelService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Listener HandleReferralFirstDeposit
 *
 * Kiểm tra và xử lý cộng hoa hồng 10% cùng với XP giới thiệu cho Referrer
 * khi người được giới thiệu (F1) nạp tiền thành công lần đầu tiên.
 * Chạy đồng bộ (sync) để thực thi an toàn trong database transaction.
 */
class HandleReferralFirstDeposit
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
        $referee = $event->user;
        $transaction = $event->transaction;

        // Nếu người dùng không được giới thiệu bởi ai thì bỏ qua
        if (!$referee->referred_by) {
            return;
        }

        try {
            // Kiểm tra xem đây có phải giao dịch nạp tiền thành công ĐẦU TIÊN của referee hay không
            $hasOtherSuccessTx = Transaction::where('user_id', $referee->id)
                ->where('status', 'SUCCESS')
                ->where('id', '!=', $transaction->id)
                ->exists();

            if ($hasOtherSuccessTx) {
                return; // Không phải nạp tiền lần đầu
            }

            $referrer = User::find($referee->referred_by);
            if (!$referrer) {
                return;
            }

            $config = config('levels.xp_rules.referral_first_deposit');
            $commissionPercent = $config['commission_percent'] ?? 10;
            $xpReferrer = $config['xp_referrer'] ?? 100;

            // Tính hoa hồng: 10% số tiền nạp
            $commissionAmount = (int) floor($transaction->amount * ($commissionPercent / 100));

            // A. Cộng hoa hồng vào số dư (balance) của Referrer
            if ($commissionAmount > 0) {
                $oldBalance = (float) $referrer->balance;
                $referrer->increment('balance', $commissionAmount);
                $newBalance = $oldBalance + $commissionAmount;

                // Ghi nhận giao dịch hoa hồng affiliate
                $transactionNo = 'AFF_' . strtoupper(Str::random(12));
                Transaction::create([
                    'user_id' => $referrer->id,
                    'amount' => $commissionAmount,
                    'fee' => 0,
                    'net_amount' => $commissionAmount,
                    'currency' => 'VND',
                    'transaction_no' => $transactionNo,
                    'status' => 'SUCCESS',
                    'payment_method' => 'AFFILIATE',
                    'order_info' => "Nhận hoa hồng giới thiệu {$commissionPercent}% nạp đầu từ thành viên {$referee->fullname} ({$referee->username})",
                    'pay_date' => now(),
                ]);

                // Gửi thông báo cho Referrer
                Notification::create([
                    'user_id' => $referrer->id,
                    'scope' => 'user',
                    'title' => 'Bạn nhận được hoa hồng giới thiệu!',
                    'message' => "Tài khoản {$referee->username} đã nạp tiền lần đầu. Bạn nhận được " . number_format($commissionAmount) . "đ hoa hồng cộng vào ví.",
                    'type' => 'success',
                    'data' => [
                        'action' => 'update_balance',
                    ],
                ]);

                // Log audit hoa hồng affiliate
                AuditLogService::log(
                    'affiliate_commission',
                    $referrer,
                    ['balance' => $oldBalance],
                    ['balance' => $newBalance, 'commission' => $commissionAmount, 'referee_id' => $referee->id],
                    $referrer->id
                );
            }

            // B. Thưởng XP giới thiệu cho Referrer
            if ($xpReferrer > 0) {
                $this->userLevelService->awardXp(
                    $referrer,
                    'referral_first_deposit',
                    $xpReferrer,
                    $referee
                );
            }

        } catch (\Throwable $e) {
            Log::error('Lỗi khi xử lý hoa hồng nạp đầu cho người giới thiệu: ' . $e->getMessage(), [
                'referrer_id' => $referee->referred_by,
                'referee_id' => $referee->id,
                'transaction_id' => $transaction->id,
            ]);
        }
    }
}
