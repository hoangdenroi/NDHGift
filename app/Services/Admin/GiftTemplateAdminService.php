<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\GiftTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GiftTemplateAdminService
{
    /**
     * Tìm kiếm, lọc và phân trang danh sách các mẫu quà tặng.
     */
    public function searchAndPaginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = GiftTemplate::query()
            ->with('category')
            ->notDeleted();

        // Lọc theo từ khóa tìm kiếm (tên hoặc code)
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('code', 'like', $search);
            });
        }

        // Lọc theo danh mục
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Lọc theo trạng thái hoạt động
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Lọc theo cờ HOT
        if (isset($filters['is_hot']) && $filters['is_hot'] !== '') {
            $query->where('is_hot', (bool) $filters['is_hot']);
        }

        // Sắp xếp mặc định: HOT lên đầu, rồi tới id giảm dần
        return $query->orderBy('is_hot', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Tạo mới một mẫu quà tặng.
     */
    public function create(array $data): GiftTemplate
    {
        $template = GiftTemplate::create($data);
        
        // Xóa cache danh sách mẫu quà tặng hoạt động ở phía client
        Cache::forget(\App\Services\GiftService::CACHE_KEY);

        return $template;
    }

    /**
     * Cập nhật thông tin mẫu quà tặng.
     */
    public function update(int $id, array $data): GiftTemplate
    {
        $template = GiftTemplate::notDeleted()->findOrFail($id);
        $template->update($data);

        // Xóa cache danh sách mẫu quà tặng hoạt động ở phía client
        Cache::forget(\App\Services\GiftService::CACHE_KEY);

        return $template;
    }

    /**
     * Thực hiện xóa mềm một mẫu quà tặng.
     */
    public function softDelete(int $id): bool
    {
        $template = GiftTemplate::notDeleted()->findOrFail($id);
        $result = $template->softDelete();

        if ($result) {
            // Xóa cache danh sách mẫu quà tặng hoạt động ở phía client
            Cache::forget(\App\Services\GiftService::CACHE_KEY);
        }

        return $result;
    }

    /**
     * Bật / Tắt trạng thái kích hoạt hoạt động.
     */
    public function toggleActive(int $id): bool
    {
        $template = GiftTemplate::notDeleted()->findOrFail($id);
        $template->is_active = !$template->is_active;
        $result = $template->save();

        if ($result) {
            // Xóa cache danh sách mẫu quà tặng hoạt động ở phía client
            Cache::forget(\App\Services\GiftService::CACHE_KEY);
        }

        return $result;
    }

    /**
     * Lấy các chỉ số thống kê của các mẫu template.
     */
    public function getStatistics(): array
    {
        return DB::table('gift_templates')
            ->selectRaw('
                COUNT(*) as total_templates,
                SUM(CASE WHEN is_active = true AND is_deleted = false THEN 1 ELSE 0 END) as active_templates,
                SUM(CASE WHEN is_hot = true AND is_deleted = false THEN 1 ELSE 0 END) as hot_templates,
                SUM(CASE WHEN is_deleted = false THEN sold ELSE 0 END) as total_sold
            ')
            ->first() ? (array) DB::table('gift_templates')
                ->selectRaw('
                    COUNT(*) filter (where is_deleted = false) as total_templates,
                    COUNT(*) filter (where is_active = true and is_deleted = false) as active_templates,
                    COUNT(*) filter (where is_hot = true and is_deleted = false) as hot_templates,
                    SUM(sold) filter (where is_deleted = false) as total_sold
                ')
                ->first() : [
                    'total_templates' => 0,
                    'active_templates' => 0,
                    'hot_templates' => 0,
                    'total_sold' => 0
                ];
    }
}
