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
        'affiliate_code',
        'referred_by',
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

    /**
     * Tự động khởi tạo mã affiliate ngẫu nhiên khi tạo user mới.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            if (empty($model->affiliate_code)) {
                $model->affiliate_code = strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }

    // ===== RELATIONS =====

    /**
     * Mối quan hệ: Một người dùng có một thông tin cấp độ (UserLevel).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userLevel(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserLevel::class);
    }

    /**
     * Mối quan hệ: Một người dùng có nhiều giao dịch tích lũy điểm kinh nghiệm (XP).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function xpTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    /**
     * Mối quan hệ: Một người dùng có thể được giới thiệu bởi một người dùng khác.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referredBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Mối quan hệ: Một người dùng giới thiệu được nhiều người dùng khác.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referrals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

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

    // ===== LEVEL & AFFILIATE GETTERS =====

    /**
     * Getter: Lấy cấp bậc hiện tại của người dùng.
     *
     * @return string
     */
    public function getCurrentTierAttribute(): string
    {
        return $this->userLevel?->tier ?? 'bronze';
    }

    /**
     * Getter: Kiểm tra xem cấp độ có đang bị đóng băng hay không.
     *
     * @return bool
     */
    public function getIsTierFrozenAttribute(): bool
    {
        return $this->userLevel?->is_frozen ?? false;
    }

    /**
     * Getter: Lấy tổng số XP hiện tại của người dùng.
     *
     * @return int
     */
    public function getCurrentXpAttribute(): int
    {
        return $this->userLevel?->total_xp ?? 0;
    }

    /**
     * Getter: Tạo đường dẫn affiliate cá nhân để chia sẻ.
     *
     * @return string
     */
    public function getAffiliateLinkAttribute(): string
    {
        $locale = session('locale', config('localization.default_locale', 'en'));
        return url("/{$locale}/register?ref=" . $this->affiliate_code);
    }
}
