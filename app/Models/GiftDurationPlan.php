<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model GiftDurationPlan — Gói thời hạn quà tặng.
 *
 * Admin cấu hình các gói (15d, 30d, 90d, vĩnh viễn) với giá khác nhau.
 * User chọn 1 gói khi tạo quà → ảnh hưởng tổng thanh toán và thời hạn link.
 */
class GiftDurationPlan extends Model
{
    use HasFactory;
    use HasBaseColumns;

    protected $table = 'gift_duration_plans';

    protected $fillable = [
        'name',
        'code',
        'duration_days',
        'price',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Một gói thời hạn được dùng bởi nhiều quà tặng.
     */
    public function userGifts(): HasMany
    {
        return $this->hasMany(UserGift::class, 'duration_plan_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Lọc gói đang hoạt động, sắp xếp theo sort_order.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_deleted', false)
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Scope: Lọc gói chưa bị xóa mềm.
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Kiểm tra gói này có phải vĩnh viễn không (duration_days = null).
     */
    public function isForever(): bool
    {
        return $this->duration_days === null;
    }

    /**
     * Xóa mềm gói thời hạn.
     */
    public function softDelete(): bool
    {
        $this->is_deleted = true;
        $this->deleted_at = now();

        return $this->save();
    }

    /**
     * Khôi phục gói thời hạn đã xóa mềm.
     */
    public function restorePlan(): bool
    {
        $this->is_deleted = false;
        $this->deleted_at = null;

        return $this->save();
    }
}
