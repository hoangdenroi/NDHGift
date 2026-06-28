<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest ToggleAnonymousRequest
 *
 * Xác thực yêu cầu toggle ẩn danh trên bảng xếp hạng.
 * Đây là action thuần toggle nên không cần tham số đầu vào,
 * chỉ cần đảm bảo user đã đăng nhập (authorize).
 */
class ToggleAnonymousRequest extends FormRequest
{
    /**
     * Chỉ cho phép user đã đăng nhập thực hiện toggle.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Không yêu cầu tham số đầu vào nào — đây là toggle action.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
