<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGiftTemplateRequest;
use App\Http\Requests\Admin\UpdateGiftTemplateRequest;
use App\Models\GiftCategory;
use App\Services\Admin\GiftTemplateAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiftTemplateController extends Controller
{
    protected GiftTemplateAdminService $templateService;

    public function __construct(GiftTemplateAdminService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'category_id', 'is_active', 'is_hot']);
        
        $templates = $this->templateService->searchAndPaginate($filters, 10);
        $categories = GiftCategory::notDeleted()->orderBy('sort_order', 'asc')->get();
        $stats = $this->templateService->getStatistics();

        return view('components.pages.admin.gift-templates.gift-template-index', compact('templates', 'categories', 'stats', 'filters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGiftTemplateRequest $request): RedirectResponse
    {
        $this->templateService->create($request->validated());

        return redirect()->route('admin.gift-templates.index')
            ->with('success', 'Thêm mẫu quà tặng mới thành công.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGiftTemplateRequest $request, string $id): RedirectResponse
    {
        $this->templateService->update((int) $id, $request->validated());

        return redirect()->route('admin.gift-templates.index')
            ->with('success', 'Cập nhật mẫu quà tặng thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $this->templateService->softDelete((int) $id);

        return redirect()->route('admin.gift-templates.index')
            ->with('success', 'Xóa mẫu quà tặng thành công.');
    }

    /**
     * Bật/Tắt trạng thái hoạt động của mẫu quà tặng.
     */
    public function toggleActive(string $id): RedirectResponse
    {
        $this->templateService->toggleActive((int) $id);

        return redirect()->route('admin.gift-templates.index')
            ->with('success', 'Cập nhật trạng thái mẫu quà tặng thành công.');
    }
}
