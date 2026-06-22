<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasBaseColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Notification
 *
 * Quản lý các thông báo của người dùng trong hệ thống.
 */
class Notification extends Model
{
    use HasBaseColumns;

    protected $fillable = [
        'user_id',
        'scope',
        'title',
        'message',
        'type',
        'related_entity_type',
        'related_entity_id',
        'is_read',
        'read_at',
        'priority',
        'action_url',
        'data',
        'expires_at',
        'unitcode',
        'metadata',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Lấy thông tin người dùng nhận thông báo.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
