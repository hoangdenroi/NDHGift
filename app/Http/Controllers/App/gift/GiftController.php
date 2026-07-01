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

    /**
     * Hiển thị trang xem thử (demo) mẫu quà tặng.
     *
     * @param string $code
     * @return View
     */
    public function demo(string $code): View
    {
        // 1. Tìm template theo code
        $giftTemplate = GiftTemplate::where('code', $code)
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->firstOrFail();

        // 2. Sinh dữ liệu mockup từ form schema defaults
        $giftData = [];
        if (isset($giftTemplate->form_schema['fields']) && is_array($giftTemplate->form_schema['fields'])) {
            foreach ($giftTemplate->form_schema['fields'] as $field) {
                $key = $field['key'] ?? $field['name'] ?? null;
                if ($key) {
                    $giftData[$key] = $field['default'] ?? '';
                }
            }
        }
        if ($code === 'winter_3d') {
            $giftData['photo_1_1'] = '/assets/images/1-1.png';
            $giftData['photo_4_3'] = '/assets/images/4-3.png';
            $giftData['photo_3_2'] = '/assets/images/3-2.png';
            $giftData['photo_16_9'] = '/assets/images/16-9.png';
            $giftData['photo_9_16'] = '/assets/images/9-16.png';
        }

        // Tách settings riêng nếu cần thiết (music_url, spiral_texts)
        $giftData['settings'] = [
            'music_url' => $giftData['music_url'] ?? 'https://assets.mixkit.co/music/preview/mixkit-beautiful-dream-493.mp3',
            'spiral_texts' => $giftData['spiral_texts'] ?? 'Mãi yêu em! 💖, Trọn đời bên nhau 💕, Yêu em nhiều hơn mỗi ngày!'
        ];

        // Fallback view path nếu file template chưa được định nghĩa
        $viewPath = "gifts.templates.{$code}";
        if (! view()->exists($viewPath)) {
            $viewPath = 'gifts.templates.heart_3d';
        }

        return view($viewPath, [
            'giftData' => $giftData,
            'giftTemplate' => $giftTemplate,
            'isDemo' => true
        ]);
    }
}
