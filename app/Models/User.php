<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUnitcode;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

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
                $model->affiliate_code = strtoupper(Str::random(8));
            }
        });
    }

    // ===== RELATIONS =====

    /**
     * Mối quan hệ: Một người dùng có một thông tin cấp độ (UserLevel).
     */
    public function userLevel(): HasOne
    {
        return $this->hasOne(UserLevel::class);
    }

    /**
     * Mối quan hệ: Một người dùng có nhiều giao dịch tích lũy điểm kinh nghiệm (XP).
     */
    public function xpTransactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    /**
     * Mối quan hệ: Một người dùng có thể được giới thiệu bởi một người dùng khác.
     */
    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Mối quan hệ: Một người dùng giới thiệu được nhiều người dùng khác.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Mối quan hệ: Một người dùng có nhiều giao dịch.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Mối quan hệ: Một người dùng có thể sử dụng nhiều mã giảm giá/quà tặng.
     */
    public function coupons(): BelongsToMany
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
     */
    public function getCurrentTierAttribute(): string
    {
        $userLevel = \Illuminate\Support\Facades\Cache::remember("user_level:{$this->id}", now()->addHours(24), function () {
            return $this->userLevel()->first();
        });

        return $userLevel?->tier ?? 'bronze';
    }

    /**
     * Getter: Kiểm tra xem cấp độ có đang bị đóng băng hay không.
     */
    public function getIsTierFrozenAttribute(): bool
    {
        $userLevel = \Illuminate\Support\Facades\Cache::remember("user_level:{$this->id}", now()->addHours(24), function () {
            return $this->userLevel()->first();
        });

        return $userLevel?->is_frozen ?? false;
    }

    /**
     * Getter: Lấy tổng số XP hiện tại của người dùng.
     */
    public function getCurrentXpAttribute(): int
    {
        $userLevel = \Illuminate\Support\Facades\Cache::remember("user_level:{$this->id}", now()->addHours(24), function () {
            return $this->userLevel()->first();
        });

        return $userLevel?->total_xp ?? 0;
    }

    /**
     * Getter: Tạo đường dẫn affiliate cá nhân để chia sẻ.
     */
    public function getAffiliateLinkAttribute(): string
    {
        if (empty($this->affiliate_code)) {
            $this->affiliate_code = strtoupper(Str::random(8));
            $this->save();
        }
        $locale = session('locale', config('localization.default_locale', 'en'));

        return url("/{$locale}/register?ref=".$this->affiliate_code);
    }
}
