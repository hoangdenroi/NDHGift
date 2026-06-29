<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\GiftCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service xử lý logic nghiệp vụ quản lý Danh mục quà tặng phía Admin.
 */
class GiftCategoryAdminService
{
    /**
     * Lấy danh sách danh mục quà tặng có filter, search, phân trang.
     * Chỉ lấy các danh mục chưa bị xóa mềm.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFilteredCategories(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = GiftCategory::query()->where('is_deleted', false);

        // Lọc theo trạng thái hoạt động (active / inactive)
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Tìm kiếm theo tên hoặc slug
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('slug', 'like', $searchTerm);
            });
        }

        return $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Tổng hợp thống kê danh mục.
     *
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        return [
            'totalCategories' => GiftCategory::where('is_deleted', false)->count(),
            'activeCategories' => GiftCategory::where('is_deleted', false)->where('is_active', true)->count(),
            'inactiveCategories' => GiftCategory::where('is_deleted', false)->where('is_active', false)->count(),
        ];
    }

    /**
     * Tạo danh mục mới.
     *
     * @param array<string, mixed> $data
     * @return GiftCategory
     */
    public function createCategory(array $data): GiftCategory
    {
        return GiftCategory::create($data);
    }

    /**
     * Cập nhật danh mục.
     *
     * @param GiftCategory $category
     * @param array<string, mixed> $data
     * @return GiftCategory
     */
    public function updateCategory(GiftCategory $category, array $data): GiftCategory
    {
        $category->update($data);

        return $category;
    }

    /**
     * Bật/tắt trạng thái hoạt động của danh mục.
     *
     * @param GiftCategory $category
     * @return GiftCategory
     */
    public function toggleActive(GiftCategory $category): GiftCategory
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return $category;
    }

    /**
     * Xóa mềm danh mục quà tặng.
     *
     * @param GiftCategory $category
     * @return bool
     */
    public function softDeleteCategory(GiftCategory $category): bool
    {
        return $category->softDelete();
    }
}
