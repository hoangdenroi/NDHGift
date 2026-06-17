<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ghi lại log thao tác trong hệ thống.
 * Sử dụng Prunable để tự động dọn dẹp bản ghi cũ > 90 ngày
 * khi chạy `php artisan model:prune`.
 */
class AuditLog extends Model
{
    use Prunable;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // ===== RELATIONSHIPS =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ===== PRUNABLE =====

    /**
     * Xóa các log cũ hơn 90 ngày — tiết kiệm dung lượng DB.
     * Chạy bằng: php artisan model:prune --model=App\\Models\\AuditLog
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(90));
    }
}
