<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model GiftReaction — Cảm xúc từ người xem trang quà.
 *
 * Không cần đăng nhập — dùng visitor_hash (IP + UA) định danh.
 * Chống spam: rate limit 10 req/phút/IP + Captcha Turnstile + unique constraint.
 */
class GiftReaction extends Model
{
    // Không dùng HasBaseColumns vì bảng này nhẹ, không cần unitcode/metadata
    public $timestamps = false;

    protected $table = 'gift_reactions';

    protected $fillable = [
        'user_gift_id',
        'reaction_type',
        'visitor_hash',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Danh sách reaction hợp lệ — dùng để validate phía backend
    public const VALID_TYPES = ['heart', 'cry', 'surprise', 'laugh', 'love'];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Reaction thuộc về quà tặng nào.
     */
    public function userGift(): BelongsTo
    {
        return $this->belongsTo(UserGift::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Lọc reaction theo quà tặng.
     */
    public function scopeForGift(Builder $query, int $userGiftId): Builder
    {
        return $query->where('user_gift_id', $userGiftId);
    }

    /**
     * Scope: Lọc reaction theo loại.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('reaction_type', $type);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Tạo visitor hash từ IP + User-Agent để định danh visitor.
     * Dùng SHA-256 cho kết quả 64 ký tự cố định.
     */
    public static function makeVisitorHash(string $ipAddress, string $userAgent): string
    {
        return hash('sha256', $ipAddress . '|' . $userAgent);
    }

    /**
     * Kiểm tra reaction_type có hợp lệ không.
     */
    public static function isValidType(string $type): bool
    {
        return in_array($type, self::VALID_TYPES, true);
    }
}
