<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Model UserGift — Quà tặng do user tạo (bảng cốt lõi).
 *
 * Luồng: chọn template → chỉnh sửa (content_data JSON linh hoạt theo form_schema) →
 * chọn hiệu ứng → thanh toán → nhận link công khai /g/{slug}.
 * Trạng thái: draft → paid → expired / disabled.
 */
class UserGift extends Model
{
    use HasFactory;
    use HasBaseColumns;

    // --- Hằng số trạng thái ---
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PAID = 'paid';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DISABLED = 'disabled';

    protected $table = 'user_gifts';

    protected $fillable = [
        'user_id',
        'template_id',
        'duration_plan_id',
        'coupon_id',
        'slug',
        'is_custom_slug',
        'content_data',
        'status',
        'base_price',
        'duration_price',
        'effects_price',
        'addons_price',
        'discount_amount',
        'total_paid',
        'paid_at',
        'scheduled_at',
        'timezone',
        'view_count',
        'last_viewed_at',
        'is_view_notification_enabled',
        'expires_at',
    ];

    protected $casts = [
        'is_custom_slug' => 'boolean',
        'content_data' => 'array',
        'base_price' => 'decimal:2',
        'duration_price' => 'decimal:2',
        'effects_price' => 'decimal:2',
        'addons_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'paid_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'last_viewed_at' => 'datetime',
        'expires_at' => 'datetime',
        'view_count' => 'integer',
        'is_view_notification_enabled' => 'boolean',
    ];

    /**
     * Tự động tạo slug 12 ký tự khi tạo bản ghi mới (nếu chưa có).
     */
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->slug)) {
                $model->slug = self::generateUniqueSlug();
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Quà tặng thuộc về user nào.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quà tặng dùng template nào.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(GiftTemplate::class, 'template_id');
    }

    /**
     * Quà tặng thuộc gói thời hạn nào.
     */
    public function durationPlan(): BelongsTo
    {
        return $this->belongsTo(GiftDurationPlan::class, 'duration_plan_id');
    }

    /**
     * Mã giảm giá đã áp dụng (nếu có).
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Danh sách hiệu ứng đã chọn cho quà này (qua pivot).
     */
    public function effects(): BelongsToMany
    {
        return $this->belongsToMany(GiftEffect::class, 'user_gift_effects')
            ->withPivot(['price_at_purchase', 'config'])
            ->withTimestamps();
    }

    /**
     * Danh sách reaction từ người xem.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(GiftReaction::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Quà đang ở trạng thái nháp (chưa thanh toán).
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope: Quà đã thanh toán.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope: Quà đã hết hạn.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope: Lọc quà theo user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Quà chưa bị xóa mềm.
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope: Quà đã paid + đang trong thời hạn hiệu lực (chưa hết hạn, hoặc vĩnh viễn).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID)
            ->where('is_deleted', false)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Kiểm tra quà có đang công khai xem được không.
     * Cần: paid + chưa hết hạn + chưa bị disabled + đã đến giờ mở khóa.
     */
    public function isViewable(): bool
    {
        // Chưa thanh toán hoặc bị vô hiệu hoá
        if ($this->status !== self::STATUS_PAID) {
            return false;
        }

        // Đã hết hạn
        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        // Chưa đến giờ mở khóa (hẹn giờ)
        if ($this->scheduled_at !== null && $this->scheduled_at->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Kiểm tra quà có đang trong chế độ đếm ngược (hẹn giờ) không.
     */
    public function isScheduledAndWaiting(): bool
    {
        return $this->status === self::STATUS_PAID
            && $this->scheduled_at !== null
            && $this->scheduled_at->isFuture();
    }

    /**
     * Tăng lượt xem và cập nhật thời gian xem cuối.
     */
    public function incrementView(): void
    {
        $this->increment('view_count');
        $this->update(['last_viewed_at' => now()]);
    }

    /**
     * Tạo slug ngẫu nhiên 12 ký tự, đảm bảo unique trong DB.
     */
    public static function generateUniqueSlug(int $length = 12): string
    {
        do {
            // Dùng Str::random cho ký tự alphanumeric an toàn URL
            $slug = strtolower(Str::random($length));
        } while (self::where('slug', $slug)->exists());

        return $slug;
    }

    /**
     * Xóa mềm quà tặng.
     */
    public function softDelete(): bool
    {
        $this->is_deleted = true;
        $this->deleted_at = now();

        return $this->save();
    }

    /**
     * Khôi phục quà tặng đã xóa mềm.
     */
    public function restoreGift(): bool
    {
        $this->is_deleted = false;
        $this->deleted_at = null;

        return $this->save();
    }
}
