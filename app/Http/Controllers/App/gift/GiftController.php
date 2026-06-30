<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\gift;

use App\Http\Controllers\Controller;
use App\Models\GiftCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class GiftController extends Controller
{
    /**
     * Hiển thị trang danh sách quà tặng (Client-side).
     */
    public function index(): View
    {
        // Lưu cache danh sách danh mục quà tặng hoạt động trong 24 giờ (86400 giây)
        $categories = Cache::remember('gift_categories_active', 86400, static function () {
            return GiftCategory::active()->get();
        });

        return view('components.pages.app.gift.gift-index', compact('categories'));
    }
}
