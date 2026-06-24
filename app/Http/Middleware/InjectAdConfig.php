<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\UserLevelService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware InjectAdConfig
 *
 * Tự động tính toán mật độ hiển thị quảng cáo dựa trên level của user
 * và chia sẻ các biến này cho toàn bộ views của hệ thống.
 */
class InjectAdConfig
{
    /**
     * Khởi tạo middleware.
     */
    public function __construct(
        protected UserLevelService $userLevelService
    ) {}

    /**
     * Xử lý request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adPercent = 100;
        $userTier = 'guest';
        $isFrozen = false;

        if (Auth::check()) {
            $user = Auth::user();
            
            // Tự động điểm danh hàng ngày cho thành viên
            $checkinResult = $this->userLevelService->checkin($user);
            if ($checkinResult) {
                session()->flash('checkin_success', $checkinResult);
                session()->put('checkin_success_shown', false);
            }

            $adPercent = $this->userLevelService->getAdPercentForUser($user);
            $userTier = $user->current_tier;
            $isFrozen = $user->is_tier_frozen;
        }

        // Chia sẻ các biến cấu hình quảng cáo cho tất cả Blade view
        view()->share('adPercent', $adPercent);
        view()->share('userTier', $userTier);
        view()->share('isTierFrozen', $isFrozen);

        return $next($request);
    }
}
