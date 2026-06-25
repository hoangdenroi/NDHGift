<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Transaction
 *
 * Quản lý các giao dịch tài chính (nạp tiền, chi tiêu, quy đổi coupon) của người dùng.
 */
class Transaction extends Model
{
    use HasBaseColumns;

    // --- Hằng số trạng thái giao dịch ---
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_CANCELLED = 'CANCELLED';

    // Giới hạn tối đa giao dịch PENDING đồng thời cho mỗi user
    public const MAX_PENDING_TRANSACTIONS = 3;

    // Thời gian hết hạn giao dịch PENDING (phút)
    public const PENDING_EXPIRY_MINUTES = 60;

    protected $fillable = [
        'unitcode',
        'user_id',
        'user_identifier',
        'amount',
        'fee',
        'net_amount',
        'currency',
        'transaction_no',
        'gateway_transaction_id',
        'bank_code',
        'status',
        'payment_method',
        'payment_code',
        'response_code',
        'order_info',
        'pay_date',
        'account_number',
        'failure_reason',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'pay_date' => 'datetime',
        'refunded_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount' => 'integer',
        'fee' => 'integer',
        'net_amount' => 'integer',
        'refunded_amount' => 'integer',
    ];

    /**
     * Lấy thông tin người dùng sở hữu giao dịch này.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================================
    // SCOPES — Đóng gói logic query tái sử dụng
    // ==========================================================

    /**
     * Lọc giao dịch đang chờ xử lý và chưa hết hạn.
     * Dùng lockForUpdate() để hỗ trợ pessimistic locking chống race condition.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopePending($query): void
    {
        $query->where('status', self::STATUS_PENDING)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Lọc giao dịch chưa hết hạn (bất kể trạng thái).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeNotExpired($query): void
    {
        $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Lọc giao dịch theo user_id.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeForUser($query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Tìm giao dịch theo mã thanh toán (payment_code) — dùng trong webhook khớp nội dung CK.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeByPaymentCode($query, string $paymentCode): void
    {
        $query->where('payment_code', $paymentCode);
    }
}
