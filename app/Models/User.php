<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Các trường được phép gán hàng loạt.
     * Lưu ý: 'role' KHÔNG nằm trong fillable — chống leo thang đặc quyền (Mass Assignment Protection).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * Các trường ẩn khi serialize (API response, toArray, v.v.)
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Type casting — đảm bảo kiểu dữ liệu chính xác khi truy xuất.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_deleted' => 'boolean',
        ];
    }

    // ===== SCOPES =====

    /**
     * Scope: chỉ lấy user đang hoạt động.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_deleted', false);
    }

    /**
     * Scope: chỉ lấy user đã bị khóa.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    // ===== HELPER METHODS =====

    /**
     * Xóa mềm tài khoản — đánh dấu is_deleted = true.
     */
    public function softDelete(): bool
    {
        $this->is_deleted = true;
        $this->deleted_at = now();

        return $this->save();
    }

    /**
     * Khôi phục tài khoản đã xóa mềm.
     */
    public function restoreAccount(): bool
    {
        $this->is_deleted = false;
        $this->deleted_at = null;

        return $this->save();
    }
}
