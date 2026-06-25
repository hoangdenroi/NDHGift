<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\BalanceUpdated;
use App\Events\TopupStatusChanged;
use App\Events\UserTopupSucceeded;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * TopupService — Đóng gói toàn bộ business logic nạp tiền.
 *
 * Tách khỏi Controller để tuân thủ Skinny Controller, Fat Service.
 * Mọi thao tác ghi dữ liệu đều bọc trong DB::transaction + pessimistic locking.
 */
class TopupService
{
    /**
     * Tạo giao dịch PENDING mới + sinh QR thanh toán.
     *
     * Kiểm tra giới hạn 3 giao dịch đồng thời, sinh mã thanh toán duy nhất 8 ký tự,
     * tạo bản ghi Transaction PENDING với expires_at = +1 giờ.
     *
     * @return array{transaction: Transaction, qr_url: string, description: string}
     *
     * @throws \RuntimeException Khi đã đạt giới hạn giao dịch PENDING
     */
    public function createPendingTransaction(User $user, int $amount): array
    {
        return DB::transaction(function () use ($user, $amount): array {
            // Kiểm tra giới hạn — lockForUpdate ngăn race condition tạo đồng thời
            $pendingTransactions = Transaction::forUser($user->id)
                ->pending()
                ->lockForUpdate()
                ->get();

            if ($pendingTransactions->count() >= Transaction::MAX_PENDING_TRANSACTIONS) {
                throw new \RuntimeException(
                    'Bạn đã có ' . Transaction::MAX_PENDING_TRANSACTIONS . ' giao dịch đang chờ. '
                    . 'Vui lòng hoàn tất hoặc hủy giao dịch cũ trước khi tạo mới.'
                );
            }

            // Sinh mã thanh toán ngắn 8 ký tự, đảm bảo unique trong DB
            $paymentCode = $this->generateUniquePaymentCode();

            // Cấu hình ngân hàng
            $bankId = config('payment.vietqr.bin', '970423');
            $accountNo = config('payment.vietqr.account', '10003179213');
            $accountName = config('payment.vietqr.name', 'NGUYEN DUC HOANG');
            $template = config('payment.vietqr.template', 'compact');
            $prefix = config('payment.vietqr.prefix', 'SEVQR ');

            // Nội dung CK chứa mã thanh toán duy nhất — giúp webhook khớp chính xác
            $description = trim($prefix) . ' ' . $paymentCode;

            // Sinh link QR từ VietQR
            $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$template}.png"
                . "?amount={$amount}"
                . '&addInfo=' . urlencode($description)
                . '&accountName=' . urlencode($accountName);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'currency' => 'VND',
                'transaction_no' => 'TXN' . Str::ulid()->toString(),
                'status' => Transaction::STATUS_PENDING,
                'payment_method' => 'SEPAY',
                'payment_code' => $paymentCode,
                'order_info' => $description,
                'expires_at' => now()->addMinutes(Transaction::PENDING_EXPIRY_MINUTES),
            ]);

            return [
                'transaction' => $transaction,
                'qr_url' => $qrUrl,
                'description' => $description,
            ];
        });
    }

    /**
     * Hủy giao dịch PENDING — chỉ cho phép owner hủy giao dịch của mình.
     *
     * Sử dụng lockForUpdate() chống race condition với webhook:
     * nếu webhook đã xử lý (status != PENDING) → không hủy được.
     *
     * @throws \RuntimeException Khi giao dịch không thuộc user hoặc không còn PENDING
     */
    public function cancelTransaction(User $user, Transaction $transaction): Transaction
    {
        return DB::transaction(function () use ($user, $transaction): Transaction {
            // Lock record để chống race condition với webhook
            $lockedTx = Transaction::where('id', $transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Kiểm tra quyền sở hữu — chống IDOR
            if ($lockedTx->user_id !== $user->id) {
                throw new \RuntimeException('Bạn không có quyền hủy giao dịch này.');
            }

            // Chỉ hủy được giao dịch đang PENDING
            if ($lockedTx->status !== Transaction::STATUS_PENDING) {
                throw new \RuntimeException(
                    'Giao dịch đã được xử lý (trạng thái: ' . $lockedTx->status . '), không thể hủy.'
                );
            }

            $lockedTx->status = Transaction::STATUS_CANCELLED;
            $lockedTx->failure_reason = 'Người dùng tự hủy giao dịch.';
            $lockedTx->save();

            // Broadcast thay đổi trạng thái → UI tự cập nhật
            event(new TopupStatusChanged(
                userId: $user->id,
                transactionId: $lockedTx->id,
                newStatus: Transaction::STATUS_CANCELLED,
            ));

            return $lockedTx;
        });
    }

    /**
     * Lấy danh sách giao dịch PENDING chưa hết hạn của user.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Transaction>
     */
    public function getPendingTransactions(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::forUser($user->id)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Xử lý webhook từ SePay — khớp giao dịch PENDING hoặc fallback về unitcode.
     *
     * Flow:
     * 1. Kiểm tra trùng lặp gateway_transaction_id
     * 2. Thử khớp payment_code từ nội dung CK → Transaction PENDING
     * 3. Fallback: thử khớp unitcode từ nội dung CK → User (tương thích cũ)
     * 4. Cộng tiền, tạo notification, broadcast events
     *
     * @param array<string, mixed> $validated Dữ liệu đã validate từ SepayWebhookRequest
     * @return array{success: bool, message: string}
     */
    public function processSepayWebhook(array $validated): array
    {
        $gatewayTransactionId = (string) $validated['id'];

        // Kiểm tra giao dịch đã xử lý chưa — tránh xử lý trùng lặp
        if (Transaction::where('gateway_transaction_id', $gatewayTransactionId)->exists()) {
            return ['success' => true, 'message' => 'Giao dịch đã được xử lý trước đó.'];
        }

        return DB::transaction(function () use ($validated, $gatewayTransactionId): array {
            $prefix = config('payment.vietqr.prefix', 'SEVQR ');
            $transferContent = strtoupper(trim($validated['content']));
            $prefixPattern = preg_quote(strtoupper(trim($prefix)), '/');

            // Chỉ xử lý tiền vào + đúng prefix
            if ($validated['transferType'] !== 'in' || !preg_match('/' . $prefixPattern . '\s*([A-Z0-9]+)/', $transferContent, $matches)) {
                return $this->createFailedWebhookTransaction(
                    $validated,
                    $gatewayTransactionId,
                    'Sai cú pháp nạp tiền hoặc không phải giao dịch chuyển vào (in)'
                );
            }

            $code = trim($matches[1]);

            // === Ưu tiên 1: Khớp payment_code của Transaction PENDING ===
            $pendingTx = Transaction::pending()
                ->byPaymentCode($code)
                ->lockForUpdate()
                ->first();

            if ($pendingTx) {
                return $this->fulfillPendingTransaction($pendingTx, $validated, $gatewayTransactionId);
            }

            // === Fallback: Khớp unitcode của User (backward-compatible) ===
            $user = User::where('unitcode', $code)->first();

            if ($user) {
                return $this->createSuccessTransaction($user, $validated, $gatewayTransactionId);
            }

            // Không khớp được → ghi nhận giao dịch FAILED
            return $this->createFailedWebhookTransaction(
                $validated,
                $gatewayTransactionId,
                'Không tìm thấy giao dịch chờ hoặc user với mã: ' . $code
            );
        });
    }

    /**
     * Chuyển giao dịch PENDING đã quá hạn sang EXPIRED.
     * Gọi từ Scheduled Command mỗi giờ.
     *
     * @return int Số giao dịch đã expire
     */
    public function expireOldTransactions(): int
    {
        $expiredTransactions = Transaction::where('status', Transaction::STATUS_PENDING)
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expiredTransactions as $tx) {
            // Lock từng record để chống race condition với webhook đang xử lý
            DB::transaction(function () use ($tx, &$count): void {
                $locked = Transaction::where('id', $tx->id)
                    ->lockForUpdate()
                    ->first();

                // Kiểm tra lại sau khi lock — có thể webhook đã xử lý xong
                if ($locked && $locked->status === Transaction::STATUS_PENDING) {
                    $locked->status = Transaction::STATUS_EXPIRED;
                    $locked->failure_reason = 'Giao dịch hết hạn tự động sau ' . Transaction::PENDING_EXPIRY_MINUTES . ' phút.';
                    $locked->save();

                    // Broadcast → UI tự cập nhật
                    if ($locked->user_id) {
                        event(new TopupStatusChanged(
                            userId: $locked->user_id,
                            transactionId: $locked->id,
                            newStatus: Transaction::STATUS_EXPIRED,
                        ));
                    }

                    $count++;
                }
            });
        }

        return $count;
    }

    // ==========================================================
    // PRIVATE METHODS — Logic nội bộ
    // ==========================================================

    /**
     * Hoàn tất giao dịch PENDING → SUCCESS: cộng tiền, notification, broadcast.
     *
     * @return array{success: bool, message: string}
     */
    private function fulfillPendingTransaction(Transaction $pendingTx, array $validated, string $gatewayTransactionId): array
    {
        /** @var User $user */
        $user = User::findOrFail($pendingTx->user_id);
        $oldBalance = $user->balance ?? 0;

        // Cộng tiền cho user
        $user->balance = $oldBalance + $validated['transferAmount'];
        $user->save();

        // Cập nhật giao dịch PENDING → SUCCESS
        $pendingTx->status = Transaction::STATUS_SUCCESS;
        $pendingTx->gateway_transaction_id = $gatewayTransactionId;
        $pendingTx->bank_code = $validated['gateway'];
        $pendingTx->response_code = '00';
        $pendingTx->pay_date = Carbon::parse($validated['transactionDate']);
        $pendingTx->account_number = $validated['subAccount'] ?? $validated['accountNumber'];
        $pendingTx->metadata = ['raw_sepay_data' => $validated];
        $pendingTx->save();

        // Ghi log, thông báo, broadcast
        $this->postSuccessActions($user, $pendingTx, $oldBalance, $validated);

        return ['success' => true, 'message' => 'Giao dịch khớp và xử lý thành công.'];
    }

    /**
     * Tạo giao dịch SUCCESS mới (fallback khi khớp unitcode trực tiếp — backward-compatible).
     *
     * @return array{success: bool, message: string}
     */
    private function createSuccessTransaction(User $user, array $validated, string $gatewayTransactionId): array
    {
        $oldBalance = $user->balance ?? 0;

        // Cộng tiền
        $user->balance = $oldBalance + $validated['transferAmount'];
        $user->save();

        $txn = Transaction::create([
            'user_id' => $user->id,
            'amount' => $validated['transferAmount'],
            'fee' => 0,
            'net_amount' => $validated['transferAmount'],
            'currency' => 'VND',
            'transaction_no' => 'TXN' . Str::ulid()->toString(),
            'gateway_transaction_id' => $gatewayTransactionId,
            'bank_code' => $validated['gateway'],
            'status' => Transaction::STATUS_SUCCESS,
            'payment_method' => 'SEPAY',
            'response_code' => '00',
            'order_info' => $validated['content'] . ' - ' . ($validated['description'] ?? ''),
            'pay_date' => Carbon::parse($validated['transactionDate']),
            'account_number' => $validated['subAccount'] ?? $validated['accountNumber'],
            'metadata' => ['raw_sepay_data' => $validated],
        ]);

        $this->postSuccessActions($user, $txn, $oldBalance, $validated);

        return ['success' => true, 'message' => 'Giao dịch thành công (fallback unitcode).'];
    }

    /**
     * Tạo giao dịch FAILED cho webhook không khớp được user/transaction.
     *
     * @return array{success: bool, message: string}
     */
    private function createFailedWebhookTransaction(array $validated, string $gatewayTransactionId, string $reason): array
    {
        Transaction::create([
            'user_id' => null,
            'amount' => $validated['transferAmount'],
            'fee' => 0,
            'net_amount' => $validated['transferAmount'],
            'currency' => 'VND',
            'transaction_no' => 'TXN' . Str::ulid()->toString(),
            'gateway_transaction_id' => $gatewayTransactionId,
            'bank_code' => $validated['gateway'],
            'status' => Transaction::STATUS_FAILED,
            'payment_method' => 'SEPAY',
            'response_code' => '99',
            'order_info' => $validated['content'] . ' - ' . ($validated['description'] ?? ''),
            'pay_date' => Carbon::parse($validated['transactionDate']),
            'account_number' => $validated['subAccount'] ?? $validated['accountNumber'],
            'metadata' => ['raw_sepay_data' => $validated],
            'failure_reason' => $reason,
        ]);

        return ['success' => true, 'message' => $reason];
    }

    /**
     * Các hành động sau khi giao dịch thành công: audit log, notification, broadcast events.
     */
    private function postSuccessActions(User $user, Transaction $txn, int|string|float $oldBalance, array $validated): void
    {
        // Ghi audit log
        AuditLogService::log(
            'topup_bank_transfer',
            $user,
            ['balance' => (float) $oldBalance],
            [
                'balance' => (float) $user->balance,
                'amount' => (float) $validated['transferAmount'],
                'gateway' => $validated['gateway'],
                'transaction_no' => $txn->gateway_transaction_id ?? $txn->transaction_no,
            ],
            $user->id
        );

        // Tạo thông báo lưu DB
        Notification::create([
            'user_id' => $user->id,
            'scope' => 'user',
            'title' => 'Nạp tiền thành công',
            'message' => 'Bạn vừa nạp thành công ' . number_format($validated['transferAmount']) . 'đ vào tài khoản.',
            'type' => 'success',
            'data' => ['action' => 'update_balance'],
        ]);

        // Broadcast số dư mới → cập nhật header/ví
        event(new BalanceUpdated(
            userId: $user->id,
            newBalance: (float) $user->balance,
            amount: (float) $validated['transferAmount'],
            message: 'Nạp thành công ' . number_format($validated['transferAmount']) . 'đ',
        ));

        // Broadcast trạng thái giao dịch → cập nhật bảng giao dịch chờ
        event(new TopupStatusChanged(
            userId: $user->id,
            transactionId: $txn->id,
            newStatus: Transaction::STATUS_SUCCESS,
        ));

        // Phát sự kiện XP & hoa hồng affiliate
        event(new UserTopupSucceeded($user, $txn));
    }

    /**
     * Sinh mã thanh toán 8 ký tự viết hoa duy nhất (chữ + số).
     * Retry tối đa 10 lần nếu trùng — xác suất trùng cực thấp (36^8 ≈ 2.8 tỷ tổ hợp).
     */
    private function generateUniquePaymentCode(): string
    {
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = strtoupper(Str::random(8));

            // Đảm bảo unique trong DB
            if (!Transaction::where('payment_code', $code)->exists()) {
                return $code;
            }
        }

        // Fallback cực hiếm — log cảnh báo
        Log::warning('TopupService: Không thể sinh payment_code duy nhất sau ' . $maxAttempts . ' lần thử.');

        return strtoupper(Str::ulid()->toString());
    }
}
