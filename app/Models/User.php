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
        'banned_reason',
        'suspended_at',
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
            'suspended_at' => 'datetime',
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
     * Mối quan hệ: Một người dùng có nhiều quà tặng đã tạo.
     */
    public function userGifts(): HasMany
    {
        return $this->hasMany(UserGift::class);
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

    /**
     * Accessor: Tự động chuyển đổi link avatar thô sang URL kích thước nhỏ (96px) để hiển thị sắc nét ở Header và danh sách nhỏ.
     *
     * @param string|null $value
     * @return string|null
     */
    public function getAvatarUrlAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Nếu là link ảnh từ các mạng xã hội bên ngoài
        if (str_starts_with($value, 'http') || str_starts_with($value, 'https')) {
            // Chuẩn hóa link avatar Google sang kích thước nhỏ 96px
            if (str_contains($value, 'googleusercontent.com')) {
                return preg_replace('/=s\d+(?:-c)?$/i', '=s96-c', $value);
            }

            // Chuẩn hóa link avatar Facebook sang kích thước nhỏ
            if (str_contains($value, 'graph.facebook.com')) {
                if (str_contains($value, 'type=large') || str_contains($value, 'type=normal')) {
                    return str_replace(['type=large', 'type=normal'], 'type=square', $value);
                }
                if (!str_contains($value, 'width=')) {
                    return $value . (str_contains($value, '?') ? '&' : '?') . 'width=96&height=96';
                }
            }

            return $value;
        }

        // Đối với ảnh tải lên cục bộ, trả về URL tuyệt đối thông qua asset()
        return asset(ltrim($value, '/'));
    }

    /**
     * Accessor: Lấy URL ảnh đại diện kích thước trung bình (240px) chuyên dùng cho Profile vòng tròn lớn.
     *
     * @return string|null
     */
    public function getAvatarUrlMdAttribute(): ?string
    {
        $value = $this->getRawOriginal('avatar_url');
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, 'http') || str_starts_with($value, 'https')) {
            // Chuẩn hóa link avatar Google sang kích thước trung bình 240px
            if (str_contains($value, 'googleusercontent.com')) {
                return preg_replace('/=s\d+(?:-c)?$/i', '=s240-c', $value);
            }

            // Chuẩn hóa link avatar Facebook sang kích thước trung bình
            if (str_contains($value, 'graph.facebook.com')) {
                if (str_contains($value, 'type=large') || str_contains($value, 'type=square')) {
                    return str_replace(['type=large', 'type=square'], 'type=normal', $value);
                }
                if (!str_contains($value, 'width=')) {
                    return $value . (str_contains($value, '?') ? '&' : '?') . 'width=240&height=240';
                }
            }

            return $value;
        }

        return asset(ltrim($value, '/'));
    }

    /**
     * Accessor: Lấy URL ảnh đại diện chất lượng cao (HD - 640px) chuyên dùng cho modal phóng to.
     *
     * @return string|null
     */
    public function getAvatarUrlHdAttribute(): ?string
    {
        $value = $this->getRawOriginal('avatar_url');
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, 'http') || str_starts_with($value, 'https')) {
            // Chuẩn hóa link avatar Google sang kích thước HD (s640)
            if (str_contains($value, 'googleusercontent.com')) {
                return preg_replace('/=s\d+(?:-c)?$/i', '=s640-c', $value);
            }

            // Chuẩn hóa link avatar Facebook sang kích thước lớn
            if (str_contains($value, 'graph.facebook.com')) {
                if (str_contains($value, 'type=normal') || str_contains($value, 'type=square')) {
                    return str_replace(['type=normal', 'type=square'], 'type=large', $value);
                }
                if (!str_contains($value, 'width=')) {
                    return $value . (str_contains($value, '?') ? '&' : '?') . 'width=640&height=640';
                }
            }

            return $value;
        }

        return asset(ltrim($value, '/'));
    }

    /**
     * Mutator: Lưu giá trị thô cho avatar_url vào database.
     *
     * @param string|null $value
     * @return void
     */
    public function setAvatarUrlAttribute(?string $value): void
    {
        $this->attributes['avatar_url'] = $value;
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

        return route('affiliate.share', ['locale' => $locale, 'ref' => $this->affiliate_code]);
    }
}
