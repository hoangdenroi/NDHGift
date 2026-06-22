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
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
