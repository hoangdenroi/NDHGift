<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate dữ liệu khi tạo người dùng mới từ trang admin.
 *
 * Bảo vệ chống: XSS (sanitize tên), Mass Assignment (role xử lý riêng trong Service),
 * Brute-force (rate limit ở middleware).
 */
class StoreUserRequest extends FormRequest
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
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,user'],
            'status' => ['required', 'string', 'in:active,suspended'],
            'balance' => ['nullable', 'numeric', 'min:0'],
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
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.in' => 'Vai trò không hợp lệ.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }

    /**
     * Làm sạch dữ liệu đầu vào trước khi validate — chống XSS.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('fullname')) {
            $this->merge([
                'fullname' => strip_tags((string) $this->input('fullname')),
            ]);
        }
    }
}
