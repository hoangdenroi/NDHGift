<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Coupon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use App\Services\Admin\CouponAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

/**
 * Controller quản lý mã giảm giá phía Admin.
 */
class CouponController extends Controller
{
    public function __construct(
        private readonly CouponAdminService $couponService
    ) {}

    /**
     * Hiển thị danh sách mã giảm giá.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['type', 'is_active', 'status', 'search']);
        $coupons = $this->couponService->getFilteredCoupons($filters);
        $stats = $this->couponService->getStats();

        return view('components.pages.admin.coupons.coupon-index', compact('coupons', 'stats'));
    }

    /**
     * Tạo mã giảm giá mới.
     */
    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $this->couponService->createCoupon($request->validated());

        return redirect()
            ->route('admin.coupons.index')
            ->with('toast_type', 'success')
            ->with('toast_message', 'Tạo mã giảm giá thành công.');
    }

    /**
     * Cập nhật mã giảm giá.
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->couponService->updateCoupon($coupon, $request->validated());

        return redirect()
            ->route('admin.coupons.index')
            ->with('toast_type', 'success')
            ->with('toast_message', 'Cập nhật mã giảm giá thành công.');
    }

    /**
     * Xóa mã giảm giá.
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('toast_type', 'success')
            ->with('toast_message', 'Đã xóa mã giảm giá.');
    }

    /**
     * Bật/tắt trạng thái hoạt động.
     */
    public function toggleActive(Coupon $coupon): RedirectResponse
    {
        $this->couponService->toggleActive($coupon);

        $message = $coupon->is_active
            ? "Đã kích hoạt mã {$coupon->code}."
            : "Đã tắt mã {$coupon->code}.";

        return redirect()
            ->route('admin.coupons.index')
            ->with('toast_type', 'success')
            ->with('toast_message', $message);
    }
}
