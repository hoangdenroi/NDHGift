<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Model đại diện cho Mẫu quà tặng 3D (Gift Template).
 */
class GiftTemplate extends Model
{
    use HasFactory;
    use HasBaseColumns;

    /**
     * Bảng tương ứng trong CSDL.
     *
     * @var string
     */
    protected $table = 'gift_templates';

    /**
     * Dùng unitcode (ULID) thay vì id cho route model binding — ẩn primary key khỏi URL.
     */
    public function getRouteKeyName(): string
    {
        return 'unitcode';
    }

    /**
     * Thuộc tính có thể gán giá trị hàng loạt (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'code',
        'name',
        'description',
        'price',
        'discount',
        'sold',
        'stars',
        'is_hot',
        'is_active',
        'demo_url',
        'guide_url',
        'video_url',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'form_schema',
        'opening_type',
    ];

    /**
     * Ép kiểu dữ liệu (casts) các thuộc tính.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_hot' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'discount' => 'integer',
        'sold' => 'integer',
        'stars' => 'integer',
        'form_schema' => 'array',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Mẫu quà tặng thuộc về một danh mục quà tặng.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(GiftCategory::class, 'category_id');
    }

    /**
     * Một template được dùng bởi nhiều quà tặng của user.
     */
    public function userGifts(): HasMany
    {
        return $this->hasMany(UserGift::class, 'template_id');
    }

    // ==========================================
    // SCOPES & HELPERS
    // ==========================================

    /**
     * Scope lọc các mẫu quà tặng đang hoạt động.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope lọc các mẫu quà tặng chưa bị xóa mềm.
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Thực hiện xóa mềm mẫu quà tặng.
     */
    public function softDelete(): bool
    {
        $this->is_deleted = true;
        $this->deleted_at = now();
        return $this->save();
    }

    /**
     * Khôi phục mẫu quà tặng đã bị xóa mềm.
     */
    public function restoreTemplate(): bool
    {
        $this->is_deleted = false;
        $this->deleted_at = null;
        return $this->save();
    }

    /**
     * Accessor cho demo_url để tự động sinh route demo nội bộ nếu trống hoặc bằng '#'.
     */
    public function getDemoUrlAttribute(?string $value): string
    {
        if (empty($value) || $value === '#') {
            return route('app.gift.demo', ['code' => $this->code]);
        }
        return $value;
    }
}
