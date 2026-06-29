<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model Coupon
 *
 * Quản lý các mã giảm giá và mã quà tặng quy đổi trực tiếp vào ví.
 */
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasBaseColumns, HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_discount',
        'min_order',
        'max_uses',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'status',
        'unitcode',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Kiểm tra mã giảm giá có hợp lệ hay không.
     *
     * @param float $orderTotal Tổng giá trị đơn hàng
     * @return bool
     */
    public function isValid(float $orderTotal = 0): bool
    {
        // 1. Kiểm tra trạng thái hoạt động
        if (!$this->is_active) {
            return false;
        }

        // 2. Chưa đến thời điểm bắt đầu hiệu lực
        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        // 3. Đã hết hạn sử dụng
        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        // 4. Đã đạt giới hạn số lần sử dụng trên toàn hệ thống
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        // 5. Giá trị đơn hàng chưa đạt mức tối thiểu yêu cầu
        if ($orderTotal < (float) $this->min_order) {
            return false;
        }

        return true;
    }

    /**
     * Tính toán số tiền được giảm giá dựa vào tổng đơn hàng.
     *
     * @param float $orderTotal Tổng giá trị đơn hàng
     * @return float Số tiền được giảm
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->type === 'percent') {
            $discount = $orderTotal * ((float) $this->value / 100);
            
            // Giới hạn số tiền giảm tối đa nếu có cấu hình
            if ($this->max_discount !== null && $discount > (float) $this->max_discount) {
                $discount = (float) $this->max_discount;
            }

            return round($discount, 2);
        }

        // Loại fixed: giảm trực tiếp số tiền cố định, không vượt quá tổng đơn hàng
        return min((float) $this->value, $orderTotal);
    }

    /**
     * Danh sách người dùng đã quy đổi hoặc áp dụng mã giảm giá này.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_user')
            ->withPivot('used_at');
    }
}
