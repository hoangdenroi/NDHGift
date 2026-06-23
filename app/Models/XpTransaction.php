<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model XpTransaction
 *
 * Ghi lại nhật ký nhận/trừ điểm kinh nghiệm (XP) của người dùng để đối soát và tránh cộng lặp.
 */
class XpTransaction extends Model
{
    use HasBaseColumns;

    /**
     * Bảng tương ứng trong database.
     *
     * @var string
     */
    protected $table = 'xp_transactions';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'unitcode',
        'user_id',
        'amount',
        'source',
        'reference_type',
        'reference_id',
        'description',
        'metadata',
    ];

    /**
     * Ép kiểu dữ liệu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'reference_id' => 'integer',
    ];

    /**
     * Mối quan hệ: Một giao dịch XP thuộc về một người dùng.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ đa hình (Polymorphic): liên kết tới đối tượng gây ra hành động (nếu có).
     *
     * @return MorphTo
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
