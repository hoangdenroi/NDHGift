<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Database\Factories\GiftCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Model GiftCategory — Danh mục quà tặng.
 *
 * Quản lý các chủ đề (Tết, Sinh nhật, Valentine...) cho Gift Templates.
 */
class GiftCategory extends Model
{
    /** @use HasFactory<GiftCategoryFactory> */
    use HasBaseColumns, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
        'unitcode',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // ===== SCOPES =====

    /**
     * Scope: Chỉ lấy các danh mục đang hoạt động và chưa bị xóa mềm.
     * Sắp xếp theo sort_order tăng dần.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_deleted', false)
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Scope: Lọc các danh mục chưa bị xóa mềm.
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    // ===== HELPER METHODS =====

    /**
     * Thực hiện xóa mềm danh mục (đánh dấu is_deleted = true).
     */
    public function softDelete(): bool
    {
        $this->is_deleted = true;
        $this->deleted_at = now();

        return $this->save();
    }

    /**
     * Khôi phục danh mục đã bị xóa mềm.
     */
    public function restoreCategory(): bool
    {
        $this->is_deleted = false;
        $this->deleted_at = null;

        return $this->save();
    }
}
