<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate dữ liệu khi cập nhật mã giảm giá.
 */
class UpdateCouponRequest extends FormRequest
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
        $couponId = $this->route('coupon');

        return [
            'code' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Z0-9_\-]+$/', Rule::unique('coupons', 'code')->ignore($couponId)],
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
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->input('code'))),
            ]);
        }
    }
}
