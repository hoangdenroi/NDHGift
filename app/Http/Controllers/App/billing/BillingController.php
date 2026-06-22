<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\billing;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Controller BillingController
 *
 * Quản lý lịch sử nạp tiền, chi tiêu, hiển thị ví tài khoản và thống kê tài chính đa chiều.
 */
class BillingController extends Controller
{
    /**
     * Hiển thị trang tổng quan hóa đơn & thanh toán.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $userId = $user->id;

        // 1. Lấy danh sách các mã giảm giá công khai đang hoạt động
        $publicCoupons = Coupon::where('is_active', true)
            ->where('status', 'public')
            ->orderBy('created_at', 'desc')
            ->get();
        $publicCouponsCount = $publicCoupons->count();

        // 2. Lấy dữ liệu thống kê nạp tiền đa chiều (Ngày, Tháng, Năm)
        
        // --- 2.1. Thống kê theo Ngày (30 ngày gần nhất) ---
        $dayTxs = Transaction::where('user_id', $userId)
            ->where('status', 'SUCCESS')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->get();
        
        $dayTxsCount = $dayTxs->count();
        $dayTxsTotal = $dayTxs->sum('amount');
        $dayTxsAvg = $dayTxsCount > 0 ? (int) round($dayTxsTotal / $dayTxsCount) : 0;

        $groupedDays = $dayTxs->groupBy(fn ($tx) => $tx->created_at->format('Y-m-d'));

        $days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $days->put($dateStr, [
                'label' => $date->format('d/m'),
                'value' => isset($groupedDays[$dateStr]) ? $groupedDays[$dateStr]->sum('amount') : 0
            ]);
        }

        // --- 2.2. Thống kê theo Tháng (12 tháng gần nhất) ---
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->put($date->format('Y-m'), [
                'label' => 'Th' . $date->format('m'),
                'value' => 0
            ]);
        }
        
        $monthTxs = Transaction::where('user_id', $userId)
            ->where('status', 'SUCCESS')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get();
            
        foreach ($monthTxs as $tx) {
            $key = $tx->created_at->format('Y-m');
            if ($months->has($key)) {
                $monthData = $months->get($key);
                $monthData['value'] += $tx->amount;
                $months->put($key, $monthData);
            }
        }
        $monthTxsCount = $monthTxs->count();
        $monthTxsTotal = $monthTxs->sum('amount');
        $monthTxsAvg = $monthTxsCount > 0 ? (int) round($monthTxsTotal / $monthTxsCount) : 0;

        // --- 2.3. Thống kê theo Năm (Các năm từ 2020 đến 2030) ---
        $years = collect();
        $currentYear = (int) now()->format('Y');
        $startYear = max(2020, $currentYear - 5);
        $endYear = min(2030, $currentYear + 5);

        for ($year = $startYear; $year <= $endYear; $year++) {
            $years->put((string) $year, [
                'label' => (string) $year,
                'value' => 0
            ]);
        }

        $yearTxs = Transaction::where('user_id', $userId)
            ->where('status', 'SUCCESS')
            ->where('created_at', '>=', Carbon::create($startYear, 1, 1)->startOfDay())
            ->where('created_at', '<=', Carbon::create($endYear, 12, 31)->endOfDay())
            ->get();

        foreach ($yearTxs as $tx) {
            $key = $tx->created_at->format('Y');
            if ($years->has($key)) {
                $yearData = $years->get($key);
                $yearData['value'] += $tx->amount;
                $years->put($key, $yearData);
            }
        }
        $yearTxsCount = $yearTxs->count();
        $yearTxsTotal = $yearTxs->sum('amount');
        $yearTxsAvg = $yearTxsCount > 0 ? (int) round($yearTxsTotal / $yearTxsCount) : 0;

        // --- 2.4. Đóng gói dữ liệu biểu đồ ---
        $chartData = [
            'day' => [
                'labels' => $days->pluck('label')->toArray(),
                'values' => $days->pluck('value')->toArray(),
                'total' => $dayTxsTotal,
                'count' => $dayTxsCount,
                'avg' => $dayTxsAvg,
            ],
            'month' => [
                'labels' => $months->pluck('label')->toArray(),
                'values' => $months->pluck('value')->toArray(),
                'total' => $monthTxsTotal,
                'count' => $monthTxsCount,
                'avg' => $monthTxsAvg,
            ],
            'year' => [
                'labels' => $years->pluck('label')->toArray(),
                'values' => $years->pluck('value')->toArray(),
                'total' => $yearTxsTotal,
                'count' => $yearTxsCount,
                'avg' => $yearTxsAvg,
            ]
        ];

        // 3. Lấy lịch sử giao dịch gần đây của người dùng (phân trang 5 bản ghi)
        $transactions = Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('components.pages.app.billing.billing-index', compact(
            'publicCouponsCount',
            'publicCoupons',
            'chartData',
            'transactions'
        ));
    }
}
