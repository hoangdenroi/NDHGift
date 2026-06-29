<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\gift;

use App\Http\Controllers\Controller;
use App\Models\GiftCategory;
use Illuminate\Contracts\View\View;

class GiftController extends Controller
{
    /**
     * Hiển thị trang danh sách quà tặng (Client-side).
     */
    public function index(): View
    {
        $categories = GiftCategory::where('is_deleted', false)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('components.pages.app.gift.gift-index', compact('categories'));
    }
}
