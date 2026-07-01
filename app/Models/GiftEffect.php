<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model GiftEffect — Hiệu ứng add-on premium.
 *
 * Admin quản lý danh sách hiệu ứng (animation, music, frame...),
 * user chọn trong trang editor, giá cộng vào tổng thanh toán.
 */
class GiftEffect extends Model
{
    use HasFactory;
    use HasBaseColumns;

    protected $table = 'gift_effects';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'price',
        'preview_url',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Hiệu ứng được chọn bởi nhiều quà tặng (qua pivot user_gift_effects).
     */
    public function userGifts(): BelongsToMany
    {
        return $this->belongsToMany(UserGift::class, 'user_gift_effects')
            ->withPivot(['price_at_purchase', 'config'])
            ->withTimestamps();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Hiệu ứng đang hoạt động, sắp xếp theo sort_order.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_deleted', false)
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Scope: Hiệu ứng chưa bị xóa mềm.
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope: Lọc theo phân loại hiệu ứng (animation, music, frame...).
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Xóa mềm hiệu ứng.
     */
    public function softDelete(): bool
    {
        $this->is_deleted = true;
        $this->deleted_at = now();

        return $this->save();
    }

    /**
     * Khôi phục hiệu ứng đã xóa mềm.
     */
    public function restoreEffect(): bool
    {
        $this->is_deleted = false;
        $this->deleted_at = null;

        return $this->save();
    }
}
