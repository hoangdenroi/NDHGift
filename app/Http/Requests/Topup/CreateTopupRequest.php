<?php

declare(strict_types=1);

namespace App\Http\Requests\Topup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate dữ liệu tạo giao dịch nạp tiền.
 *
 * Chỉ nhận amount từ client — mọi tính toán giá, phí
 * đều thực hiện 100% phía server (Zero-Trust Client).
 */
class CreateTopupRequest extends FormRequest
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
            // Giới hạn 20.000đ → 100.000.000đ, chỉ nhận số nguyên
            'amount' => 'required|integer|min:20000|max:100000000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Vui lòng nhập số tiền muốn nạp.',
            'amount.integer' => 'Số tiền phải là số nguyên.',
            'amount.min' => 'Số tiền tối thiểu là 20.000đ.',
            'amount.max' => 'Số tiền tối đa là 100.000.000đ.',
        ];
    }
}
