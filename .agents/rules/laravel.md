---
trigger: always_on
---

# Laravel & PHP Coding Rules

## 1. Tiêu chuẩn viết code (Coding Standards)

- Luôn tuân thủ tiêu chuẩn PSR-12, tomicity.
- Luôn thêm `declare(strict_types=1);` ở đầu mọi file PHP.
- Sử dụng Type Hinting và Return Types rõ ràng cho mọi function/method.
- Đặt tên biến, hàm theo `camelCase`; tên class, interface, trait theo `PascalCase`.
- Tên table database dùng `snake_case` số nhiều.

## 2. Kiến trúc & Design Pattern

- **Skinny Controllers, Fat Services:** Controller chỉ làm nhiệm vụ nhận Request và trả về Response. Mọi logic nghiệp vụ (business logic) phải được đưa vào các lớp `Service` hoặc `Action`.
- Tránh viết logic phức tạp trực tiếp trong Model. Model chỉ chứa relationships, scopes, casts và mutators/accessors.
- Sử dụng **Repository Pattern** nếu dự án yêu cầu truy xuất dữ liệu phức tạp từ nhiều nguồn, nếu không, dùng trực tiếp Eloquent nhưng phải đóng gói gọn gàng.
- Khi tạo ra các bảng mới đánh index những cột quan trọng và query nhiều.

## 3. Database & Eloquent

- **Tuyệt đối tránh lỗi N+1 Query:** Luôn sử dụng Eager Loading (`with()`, `load()`) khi gọi relationships trong vòng lặp.
- Không sử dụng các raw query trừ khi bắt buộc để tối ưu performance. Luôn ưu tiên Query Builder và Eloquent.
- Đặt các query thường dùng vào Scope của Model (`scopeActive`, `scopePublished`,...).
- Sử dụng Database Transactions (`DB::transaction`) cho các thao tác ghi dữ liệu liên quan đến nhiều bảng.

## 4. Bảo mật (Security) & Validation

- Bảo mật là ưu tiên số 1, tránh lỗi xss, sql injection,...
- **Không bao giờ validate trực tiếp trong Controller.** Luôn tạo và sử dụng `FormRequest` để validate dữ liệu đầu vào.
- Không bao giờ tin tưởng dữ liệu người dùng (Sanitize & Validate).
- Xử lý phân quyền bằng Laravel Policies hoặc Gates, không hardcode logic check quyền (`if ($user->role == 'admin')`) rải rác trong code.

## 5. Xử lý lỗi (Error Handling)

- Không dùng `die()`, `dd()`, `dump()` trong code production.
- Trả về đúng HTTP Status Code cho API (200 OK, 201 Created, 400 Bad Request, 403 Forbidden, 404 Not Found, 422 Unprocessable Entity, 500 Internal Server Error).
- Bọc các đoạn code dễ sinh lỗi trong khối `try-catch` và log lỗi lại bằng `Log::error()`.

## 6. Trả lời & Tư duy (Agent Behavior)

- Đọc kỹ yêu cầu, suy nghĩ từng bước trước khi viết code.
- Nếu yêu cầu không rõ ràng hoặc thiếu logic, phải hỏi lại người dùng thay vì tự ý đoán.
- Code sinh ra phải đúng trọng tâm, không giải thích lan man, comment code ngắn gọn và mang tính giải thích "tại sao làm thế này" chứ không phải "đang làm gì".
- Luôn viết test sau khi làm chức năng mới, chạy test.
