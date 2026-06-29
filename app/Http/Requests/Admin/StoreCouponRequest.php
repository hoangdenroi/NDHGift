<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate dữ liệu khi tạo mã giảm giá mới.
 *
 * Đặc biệt: max_discount bắt buộc khi type=percent, expires_at phải sau starts_at.
 */
class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Z0-9_\-]+$/', 'unique:coupons,code'],
            'type' => ['required', 'string', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'min_order' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['boolean'],
            'status' => ['required', 'string', 'in:public,private'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Mã giảm giá là bắt buộc.',
            'code.regex' => 'Mã chỉ chứa chữ in hoa, số, gạch ngang và gạch dưới.',
            'code.unique' => 'Mã giảm giá đã tồn tại.',
            'type.in' => 'Loại mã không hợp lệ.',
            'value.required' => 'Giá trị là bắt buộc.',
            'expires_at.after' => 'Ngày hết hạn phải sau ngày bắt đầu.',
            'status.in' => 'Phạm vi không hợp lệ.',
        ];
    }

    /**
     * Tự động uppercase mã code trước khi validate — chuẩn hóa dữ liệu.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->input('code'))),
            ]);
        }
    }
}
