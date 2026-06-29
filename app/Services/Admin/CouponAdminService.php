<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service xử lý logic nghiệp vụ quản lý Coupon phía Admin.
 */
class CouponAdminService
{
    /**
     * Lấy danh sách coupon có filter, search, phân trang.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFilteredCoupons(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Coupon::query();

        // Lọc theo loại coupon (percent / fixed)
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Lọc theo trạng thái hoạt động
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Lọc theo phạm vi hiển thị (public / private)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Tìm kiếm theo mã code
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where('code', 'like', $searchTerm);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    /**
     * Tổng hợp thống kê coupon.
     *
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        return [
            'totalCoupons' => Coupon::count(),
            'activeCoupons' => Coupon::where('is_active', true)->count(),
            'expiredCoupons' => Coupon::where('expires_at', '<', now())
                ->where('expires_at', '!=', null)
                ->count(),
            'totalUsed' => (int) Coupon::sum('used_count'),
        ];
    }

    /**
     * Tạo mã giảm giá mới.
     *
     * @param array<string, mixed> $data
     * @return Coupon
     */
    public function createCoupon(array $data): Coupon
    {
        return Coupon::create($data);
    }

    /**
     * Cập nhật mã giảm giá.
     *
     * @param Coupon $coupon
     * @param array<string, mixed> $data
     * @return Coupon
     */
    public function updateCoupon(Coupon $coupon, array $data): Coupon
    {
        $coupon->update($data);

        return $coupon;
    }

    /**
     * Bật/tắt trạng thái hoạt động của coupon.
     *
     * @param Coupon $coupon
     * @return Coupon
     */
    public function toggleActive(Coupon $coupon): Coupon
    {
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        return $coupon;
    }
}
