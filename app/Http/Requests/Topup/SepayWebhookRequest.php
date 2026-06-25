<?php

declare(strict_types=1);

namespace App\Http\Requests\Topup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate dữ liệu webhook từ SePay.
 *
 * Tách logic validate ra khỏi controller, tuân thủ kiến trúc Skinny Controller.
 * Lưu ý: Authorization xác thực API key được xử lý riêng trong Service
 * vì header check không thuộc FormRequest scope.
 */
class SepayWebhookRequest extends FormRequest
{
    /**
     * Webhook từ bên ngoài, không cần auth user.
     */
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
            'id' => 'required|integer',
            'gateway' => 'required|string|max:100',
            'transactionDate' => 'required|string|max:50',
            'accountNumber' => 'required|string|max:50',
            'code' => 'nullable|string|max:100',
            'content' => 'required|string|max:500',
            'transferType' => 'required|string|in:in,out',
            'transferAmount' => 'required|integer|min:1',
            'accumulated' => 'required|integer',
            'subAccount' => 'nullable|string|max:50',
            'referenceCode' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
