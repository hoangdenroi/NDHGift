<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validate dữ liệu khi cập nhật danh mục quà tặng từ admin.
 */
class UpdateGiftCategoryRequest extends FormRequest
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
        $categoryId = $this->route('gift_category') ? $this->route('gift_category')->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('gift_categories', 'slug')->ignore($categoryId),
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'slug.required' => 'Đường dẫn slug là bắt buộc.',
            'slug.unique' => 'Đường dẫn slug đã tồn tại.',
            'slug.regex' => 'Đường dẫn slug không hợp lệ (chỉ chứa chữ thường, số và dấu gạch ngang, ví dụ: sinh-nhat-3d).',
            'sort_order.integer' => 'Thứ tự sắp xếp phải là số nguyên.',
            'sort_order.min' => 'Thứ tự sắp xếp tối thiểu là 0.',
        ];
    }

    /**
     * Làm sạch dữ liệu và tự động tạo slug nếu trống.
     */
    protected function prepareForValidation(): void
    {
        $name = $this->input('name') ? strip_tags((string) $this->input('name')) : null;
        $slug = $this->input('slug') ? trim(strip_tags((string) $this->input('slug'))) : null;

        if ($name && ($slug === null || $slug === '')) {
            $slug = Str::slug($name);
        }

        $this->merge([
            'name' => $name,
            'slug' => $slug,
            'description' => $this->input('description') ? strip_tags((string) $this->input('description')) : null,
            'icon' => $this->input('icon') ? strip_tags((string) $this->input('icon')) : null,
            'meta_title' => $this->input('meta_title') ? strip_tags((string) $this->input('meta_title')) : null,
            'meta_description' => $this->input('meta_description') ? strip_tags((string) $this->input('meta_description')) : null,
            'meta_keywords' => $this->input('meta_keywords') ? strip_tags((string) $this->input('meta_keywords')) : null,
            'is_active' => $this->has('is_active') ? (bool) $this->input('is_active') : true,
        ]);
    }
}
