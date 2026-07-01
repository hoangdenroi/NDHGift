<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\gift;

use App\Http\Controllers\Controller;
use App\Models\GiftCategory;
use App\Models\GiftDurationPlan;
use App\Models\GiftEffect;
use App\Models\GiftTemplate;
use App\Services\GiftService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class GiftController extends Controller
{
    /**
     * Hiển thị trang danh sách quà tặng (Client-side).
     */
    public function index(GiftService $giftService): View
    {
        // Lưu cache danh sách danh mục quà tặng hoạt động trong 24 giờ (86400 giây)
        $categories = Cache::remember('gift_categories_active', 86400, static function () {
            return GiftCategory::active()->get();
        });

        // Lấy danh sách mẫu quà tặng hoạt động từ service (đã được cache bên trong service)
        $gifts = $giftService->getActiveTemplatesForClient();

        return view('components.pages.app.gift.gift-index', compact('categories', 'gifts'));
    }

    /**
     * Hiển thị trang chỉnh sửa nội dung quà tặng trước khi thanh toán.
     *
     * Validate: template phải tồn tại, active, chưa bị xóa mềm và có form_schema.
     */
    public function create(string $locale, GiftTemplate $giftTemplate): View
    {
        // Chặn truy cập template bị vô hiệu hoá hoặc xóa mềm
        abort_unless($giftTemplate->is_active && ! $giftTemplate->is_deleted, 404);

        // Lấy danh sách gói thời hạn đang hoạt động (cache 24h)
        $durationPlans = Cache::remember('gift_duration_plans_active', 86400, static function () {
            return GiftDurationPlan::active()->get();
        });

        // Lấy danh sách hiệu ứng đang hoạt động (cache 24h)
        $giftEffects = Cache::remember('gift_effects_active', 86400, static function () {
            return GiftEffect::active()->get();
        });

        return view('components.pages.app.gift.gift-crud.gift-create', compact('giftTemplate', 'durationPlans', 'giftEffects'));
    }

    /**
     * Hiển thị chi tiết mẫu quà tặng.
     */
    public function show(string $locale, GiftTemplate $giftTemplate): View
    {
        // Chặn truy cập template bị vô hiệu hoá hoặc xóa mềm
        abort_unless($giftTemplate->is_active && ! $giftTemplate->is_deleted, 404);

        // Tính toán các thuộc tính bổ sung
        $imagePath = 'assets/images/gifts/'.$giftTemplate->code.'.png';
        $imageUrl = file_exists(public_path($imagePath))
            ? asset($imagePath)
            : asset('assets/images/gifts/heart_3d.png');

        $oldPrice = $giftTemplate->discount > 0
            ? $giftTemplate->price / (1 - $giftTemplate->discount / 100)
            : $giftTemplate->price;
        $oldPrice = round((float) $oldPrice, 2);

        // URL tạo quà tặng
        $createUrl = route('app.gift.create', [
            'locale' => $locale,
            'giftTemplate' => $giftTemplate->unitcode,
        ]);

        return view('components.pages.app.gift.gift-show', compact('giftTemplate', 'imageUrl', 'oldPrice', 'createUrl'));
    }
}
