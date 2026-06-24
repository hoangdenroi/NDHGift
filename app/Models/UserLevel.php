<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model UserLevel
 *
 * Quản lý thông tin cấp bậc, XP tích lũy và thời hạn hoạt động của người dùng.
 */
class UserLevel extends Model
{
    use HasBaseColumns;

    /**
     * Bảng tương ứng trong database.
     *
     * @var string
     */
    protected $table = 'user_levels';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'unitcode',
        'user_id',
        'total_xp',
        'tier',
        'is_frozen',
        'last_xp_earned_at',
        'tier_achieved_at',
        'metadata',
    ];

    /**
     * Ép kiểu dữ liệu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_xp' => 'integer',
        'is_frozen' => 'boolean',
        'last_xp_earned_at' => 'datetime',
        'tier_achieved_at' => 'datetime',
    ];

    /**
     * Mối quan hệ: Một cấp bậc thuộc về một người dùng.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tự động xóa cache cấp độ người dùng khi thông tin thay đổi.
     */
    protected static function booted(): void
    {
        static::saved(function ($userLevel): void {
            \Illuminate\Support\Facades\Cache::forget("user_level:{$userLevel->user_id}");
        });

        static::deleted(function ($userLevel): void {
            \Illuminate\Support\Facades\Cache::forget("user_level:{$userLevel->user_id}");
        });
    }
}
