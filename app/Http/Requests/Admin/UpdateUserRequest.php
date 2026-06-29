<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate dữ liệu khi cập nhật thông tin người dùng từ trang admin.
 *
 * Khác với StoreUserRequest: password optional, unique ignore bản ghi hiện tại.
 */
class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user');

        return [
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]+$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,user'],
            'status' => ['required', 'string', 'in:active,suspended'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Tên đăng nhập là bắt buộc.',
            'username.alpha_dash' => 'Tên đăng nhập chỉ chứa chữ, số, gạch ngang và gạch dưới.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',
            'fullname.required' => 'Họ tên là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'email.unique' => 'Email đã được sử dụng.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('fullname')) {
            $this->merge([
                'fullname' => strip_tags((string) $this->input('fullname')),
            ]);
        }
    }
}
