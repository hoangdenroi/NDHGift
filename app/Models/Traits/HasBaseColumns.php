<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Str;

/**
 * Trait HasBaseColumns
 *
 * Tự động tạo unitcode (ULID) khi lưu bản ghi mới và tự động ép kiểu các cột cơ sở.
 */
trait HasBaseColumns
{
    /**
     * Khởi tạo các logic tự động hóa khi tạo model.
     */
    protected static function bootHasBaseColumns(): void
    {
        static::creating(function ($model): void {
            if (empty($model->unitcode)) {
                $model->unitcode = (string) Str::ulid();
            }
        });
    }

    /**
     * Tự động ép kiểu dữ liệu cho metadata và is_deleted.
     */
    public function initializeHasBaseColumns(): void
    {
        $this->mergeCasts([
            'metadata' => 'json',
            'is_deleted' => 'boolean',
        ]);
    }
}
