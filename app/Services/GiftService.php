<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GiftTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service xử lý các logic nghiệp vụ liên quan đến mẫu quà tặng ở phía client.
 */
class GiftService
{
    /**
     * Tên khóa cache cho danh sách mẫu quà tặng hoạt động.
     */
    public const CACHE_KEY = 'gift_templates_active';

    /**
     * Thời gian sống của cache (1 giờ).
     */
    public const CACHE_TTL = 3600;

    /**
     * Lấy danh sách mẫu quà tặng đang hoạt động từ cache hoặc database.
     */
    public function getActiveTemplatesForClient(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, static function () {
            return GiftTemplate::query()
                ->active()
                ->notDeleted()
                ->with('category')
                ->get()
                ->map(static function (GiftTemplate $gift) {
                    // Xác định đường dẫn và URL ảnh preview dựa trên mã code của template
                    $imagePath = 'assets/images/gifts/'.$gift->code.'.png';
                    $imageUrl = file_exists(public_path($imagePath))
                        ? asset($imagePath)
                        : asset('assets/images/gifts/heart_3d.png');

                    // Tính toán giá gốc (old_price) dựa trên giá bán và phần trăm giảm giá
                    $oldPrice = $gift->discount > 0
                        ? $gift->price / (1 - $gift->discount / 100)
                        : $gift->price;

                    return [
                        'id' => $gift->id,
                        'title' => $gift->name,
                        'category' => $gift->category?->slug ?? '',
                        'sold' => $gift->sold,
                        'stars' => $gift->stars,
                        'is_hot' => $gift->is_hot,
                        'image' => $imageUrl,
                        'old_price' => round((float) $oldPrice, 2),
                        'price' => (float) $gift->price,
                        'discount' => $gift->discount,
                        'demo_url' => ($gift->demo_url && $gift->demo_url !== '#') ? $gift->demo_url : route('app.gift.demo', ['code' => $gift->code]),
                        'guide_url' => $gift->guide_url ?? '#',
                        'video_url' => $gift->video_url ?? '#',
                        'create_url' => route('app.gift.create', [
                            'locale' => app()->getLocale(),
                            'giftTemplate' => $gift->unitcode,
                        ]),
                        'show_url' => route('app.gift.show', [
                            'locale' => app()->getLocale(),
                            'giftTemplate' => $gift->unitcode,
                        ]),
                    ];
                });
        });
    }

    /**
     * Xóa cache danh sách mẫu quà tặng.
     */
    public function clearActiveTemplatesCache(): bool
    {
        return Cache::forget(self::CACHE_KEY);
    }
}
