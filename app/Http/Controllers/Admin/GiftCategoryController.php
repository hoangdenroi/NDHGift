<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGiftCategoryRequest;
use App\Http\Requests\Admin\UpdateGiftCategoryRequest;
use App\Models\GiftCategory;
use App\Services\Admin\GiftCategoryAdminService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller quản lý danh mục quà tặng phía Admin.
 */
class GiftCategoryController extends Controller
{
    public function __construct(
        private readonly GiftCategoryAdminService $categoryService
    ) {}

    /**
     * Hiển thị danh sách danh mục quà tặng.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['is_active', 'search']);
        $categories = $this->categoryService->getFilteredCategories($filters);
        $stats = $this->categoryService->getStats();

        return view('components.pages.admin.gift-categories.gift-category-index', compact('categories', 'stats'));
    }

    /**
     * Tạo danh mục quà tặng mới.
     */
    public function store(StoreGiftCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->createCategory($request->validated());

        return redirect()
            ->route('admin.gift-categories.index')
            ->with('toast_type', 'success')
            ->with('toast_message', 'Tạo danh mục quà tặng thành công.');
    }

    /**
     * Cập nhật danh mục quà tặng.
     */
    public function update(UpdateGiftCategoryRequest $request, GiftCategory $giftCategory): RedirectResponse
    {
        $this->categoryService->updateCategory($giftCategory, $request->validated());

        return redirect()
            ->route('admin.gift-categories.index')
            ->with('toast_type', 'success')
            ->with('toast_message', 'Cập nhật danh mục quà tặng thành công.');
    }

    /**
     * Xóa mềm danh mục quà tặng.
     */
    public function destroy(GiftCategory $giftCategory): RedirectResponse
    {
        $this->categoryService->softDeleteCategory($giftCategory);

        return redirect()
            ->route('admin.gift-categories.index')
            ->with('toast_type', 'success')
            ->with('toast_message', 'Đã xóa danh mục quà tặng.');
    }

    /**
     * Bật/tắt trạng thái hoạt động của danh mục.
     */
    public function toggleActive(GiftCategory $giftCategory): RedirectResponse
    {
        $this->categoryService->toggleActive($giftCategory);

        $message = $giftCategory->is_active
            ? "Đã kích hoạt danh mục {$giftCategory->name}."
            : "Đã tắt danh mục {$giftCategory->name}.";

        return redirect()
            ->route('admin.gift-categories.index')
            ->with('toast_type', 'success')
            ->with('toast_message', $message);
    }
}
