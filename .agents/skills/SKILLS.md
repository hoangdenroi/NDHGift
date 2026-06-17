# Core Skills & Proficiencies: PHP / Laravel Developer

# Language

- Luôn trả lời bằng Tiếng Việt, cả những file: task, Walkthrough,... comment code.

## 1. Ngôn ngữ & Framework

- **PHP 8.2+**: Thành thạo các tính năng mới (Readonly classes, Enums, Match expressions, Constructor property promotion, Nullsafe operator).
- **Laravel 11+ / 12+**: Nắm vững hệ sinh thái Laravel, vòng đời request (Request Lifecycle), Service Container, Service Providers, Facades, Events & Listeners, Jobs, Queues, Transaction,...

## 2. API Development

- Thiết kế RESTful API chuẩn mực.
- Sử dụng thành thạo `Eloquent API Resources` để format JSON response.
- Xử lý xác thực API bằng Laravel Sanctum hoặc Laravel Passport.

## 3. Database & Caching

- **Tương thích RDBMS đa nền tảng**: PostgreSQL. Khi viết raw query bằng `selectRaw()`, **tuyệt đối không sử dụng dấu backtick (`` ` ``)** và không dùng từ khóa hệ thống (như `all`, `select`) làm định danh cột. Luôn đổi tên rõ nghĩa (aliases) như `all_count`, `active_count` để mã nguồn chạy hoàn hảo 100% trên PostgreSQL.
- **Selective Caching (Cache chọn lọc)**:
    - Cache các dữ liệu tĩnh dùng chung của hệ thống để tối ưu RAM/CPU qua `Cache::remember`.
    - **Tuyệt đối không cache gộp thông tin cá nhân** của người dùng (SSH Keys, ví tiền, mật khẩu) để tránh rò rỉ dữ liệu chéo.
    - Các dữ liệu động nhạy cảm (mật khẩu mới, trạng thái hoạt động thực tế) phải luôn được truy vấn thời gian thực từ CSDL chứ không đọc bộ đệm.
- Quản lý Queue và Background Jobs (Redis, Beanstalkd, database queues).
- Khi viết migrations luôn sử dụng baseColumns (tự định nghĩa trong app\Providers\AppServiceProvider.php) để đảm bảo tính nhất quán của hệ thống.
- Đánh index hợp lý cho các cột hay được truy vấn nhiều nhất.

## 4. Frontend & Tích hợp (Tùy chọn)

- Hiểu biết về Inertia.js và Livewire để xây dựng SPA/reactive components.
- Nắm vững Blade Templating Engine.
- Tích hợp tốt với các framework frontend hiện đại: Vue.js, React, Tailwind CSS. Để sau này nâng cấp lên fullstack dễ dàng.
- Nên ưu tiên dùng Tailwind CSS để code nhanh và đẹp hơn.
- Luôn sử dụng Tailwind CSS để style cho các component (Button, Input, Modal, Tooltip,...), luôn sử dụng màu theme của hệ thống tránh lệch màu
- sử dụng các cấu hình có sẵn ở views/components/shared/ui để đồng bộ style toàn hệ thống
- Luôn sử dụng icon của google: https://fonts.google.com/icons.
-

## 5. Testing & Debugging

- **Pest PHP / PHPUnit**: Viết Unit Tests cho các logic độc lập và Feature Tests cho các API/Controller và lưu nó vào thư mục tests.
- Áp dụng Test-Driven Development (TDD) khi được yêu cầu.
- Debugging hiệu quả bằng Laravel Telescope, Ray, hoặc Xdebug.

## 6. DevOps & Deployment cơ bản

- Nắm rõ cách config file `.env`, tối ưu hóa ứng dụng bằng `php artisan optimize`.
- Hiểu cách hoạt động của Laravel Forge, Envoyer hoặc Docker/Sail.

## 7. Lưu trữ code

- Luôn thực hiện commit sau những thay đổi quan trọng và ý nghĩa
- Commit message phải bằng Tiếng Việt
- Không thực hiện commit nhiều thay đổi nhỏ lẻ, gộp lại thành 1 commit có ý nghĩa
