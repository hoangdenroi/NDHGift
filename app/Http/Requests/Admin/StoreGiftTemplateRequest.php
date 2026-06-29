<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGiftTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:gift_categories,id',
            'code' => 'required|string|max:100|unique:gift_templates,code|regex:/^[a-z0-9_-]+$/',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'is_hot' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'demo_url' => 'nullable|string|max:255',
            'guide_url' => 'nullable|string|max:255',
            'video_url' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ];
    }

    /**
     * Tùy biến thông báo lỗi validate.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Danh mục bắt buộc phải chọn.',
            'category_id.exists' => 'Danh mục chọn không tồn tại.',
            'code.required' => 'Mã code giao diện bắt buộc nhập.',
            'code.unique' => 'Mã code này đã được sử dụng.',
            'code.regex' => 'Mã code chỉ chứa chữ thường, số, dấu gạch ngang hoặc gạch dưới.',
            'name.required' => 'Tên mẫu quà tặng không được để trống.',
            'price.required' => 'Giá tiền bắt buộc nhập.',
            'price.numeric' => 'Giá tiền phải là một con số.',
            'price.min' => 'Giá tiền không được nhỏ hơn 0.',
            'discount.min' => 'Chiết khấu không được nhỏ hơn 0%.',
            'discount.max' => 'Chiết khấu không được lớn hơn 100%.',
        ];
    }

    /**
     * Chuẩn bị dữ liệu trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        // Loại bỏ thẻ HTML thô chống XSS tấn công
        $this->merge([
            'name' => strip_tags((string) $this->input('name')),
            'code' => strtolower(strip_tags((string) $this->input('code'))),
            'description' => $this->filled('description') ? strip_tags((string) $this->input('description')) : null,
            'is_hot' => $this->has('is_hot') ? 1 : 0,
            'is_active' => $this->has('is_active') ? 1 : 0,
            'discount' => $this->filled('discount') ? (int) $this->input('discount') : 0,
            'meta_title' => $this->filled('meta_title') ? strip_tags((string) $this->input('meta_title')) : null,
            'meta_description' => $this->filled('meta_description') ? strip_tags((string) $this->input('meta_description')) : null,
            'meta_keywords' => $this->filled('meta_keywords') ? strip_tags((string) $this->input('meta_keywords')) : null,
        ]);
    }
}
