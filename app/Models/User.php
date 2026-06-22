<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUnitcode;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUnitcode, Notifiable;

    /**
     * Các trường được phép gán hàng loạt.
     * Lưu ý: 'role' KHÔNG nằm trong fillable — chống leo thang đặc quyền (Mass Assignment Protection).
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'fullname',
        'email',
        'unitcode',
        'phone',
        'avatar_url',
        'balance',
        'password',
        'google_id',
        'facebook_id',
        'status',
        'settings',
        'last_change_password_at',
        'last_login_at',
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
            'last_change_password_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:4',
            'is_deleted' => 'boolean',
            'settings' => 'array',
            'metadata' => 'array',
        ];
    }

    // ===== RELATIONS =====

    /**
     * Mối quan hệ: Một người dùng có nhiều giao dịch.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Mối quan hệ: Một người dùng có thể sử dụng nhiều mã giảm giá/quà tặng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function coupons(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_user')
            ->withPivot('used_at');
    }

    // ===== SCOPES =====

    /**
     * Scope: chỉ lấy user đang hoạt động (chưa bị khóa, chưa xóa mềm).
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_deleted', false);
    }

    /**
     * Scope: chỉ lấy user đã bị tạm khóa.
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

    /**
     * Kiểm tra tài khoản có đang hoạt động bình thường.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->is_deleted;
    }

    /**
     * Kiểm tra có phải admin không.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
