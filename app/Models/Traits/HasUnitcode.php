<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Str;

/**
 * Trait HasUnitcode
 * Tự động tạo unitcode (ULID) khi khởi tạo model.
 * Dùng cho User (kế thừa Authenticatable) hoặc model không dùng BaseModel.
 */
trait HasUnitcode
{
    public static function bootHasUnitcode(): void
    {
        static::creating(function ($model): void {
            if (empty($model->unitcode)) {
                $model->unitcode = (string) Str::ulid();
            }
        });
    }
}
