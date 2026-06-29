# Kế Hoạch Triển Khai Kiến Trúc Quà Tặng Động (Hybrid) Cho NDHGift

Tài liệu này chi tiết hóa giải pháp kiến trúc lai (Hybrid Architecture) kết hợp giữa sức mạnh lưu trữ/quản lý của Laravel API và sự linh hoạt của các Static 3D/Interactive Templates trong thư mục `public`.

---

## 📐 Thiết Kế Kiến Trúc Hệ Thống (Architecture Design)

```mermaid
graph TD
    subgraph Client-side (Browser)
        View[Trang xem quà tặng /g/uuid]
        Static[Static Templates public/templates/*]
        Form[Dynamic Form sinh tự động từ manifest.json]
    end

    subgraph Laravel Backend (Server)
        API[Laravel API /api/gifts/uuid]
        Auth[Auth & Payments]
        Admin[Admin CRUD Categories & Templates]
    end

    subgraph Cloud Infrastructure
        R2[(Cloudflare R2 / S3)]
        CF[Cloudflare CDN Proxy]
    end

    Static -->|1. Fetch Gift Data| API
    API -->|2. Trả JSON đã Sanitize| Static
    Form -->|Upload trực tiếp ảnh/nhạc| R2
    R2 -->|Bypass Laravel| Form
    Form -->|Lưu link file + Lời chúc| API
    CF -->|Cache & Tải nhanh Assets 3D| Static
```

---

## 🗄️ Thiết Kế Cơ Sở Dữ Liệu (Database Schema)

> [!IMPORTANT]
> Toàn bộ các bảng trong NDHGift đều sử dụng `$table->baseColumns()` trong migration thay vì các trường id, timestamps, softDeletes riêng lẻ. Các Model tương ứng sẽ sử dụng trait `HasBaseColumns` để đồng bộ.

### 1. Bảng Danh mục Quà tặng (`gift_categories`)
Quản lý các chủ đề lớn (Tết, Sinh nhật, Tình yêu...).
```php
Schema::create('gift_categories', function (Blueprint $table) {
    $table->baseColumns();               // Khởi tạo id, unitcode, metadata, is_deleted, deleted_at, timestamps
    $table->string('name');              // Raw key để dịch, vd: "Birthday", "Love"
    $table->string('slug')->unique();    // Unique slug làm URL
    $table->text('description')->nullable();
    $table->string('icon')->nullable();  // Google Material Symbol name (vd: cake, favorite)
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    
    // SEO Fields
    $table->string('meta_title')->nullable();
    $table->string('meta_description')->nullable();
    $table->string('meta_keywords')->nullable();
});
```

### 2. Bảng Mẫu Quà tặng (`gift_templates`)
Lưu trữ thông tin cấu hình kinh doanh (Giá cả, discount, trạng thái) của từng mẫu 3D.
```php
Schema::create('gift_templates', function (Blueprint $table) {
    $table->baseColumns();                // Khởi tạo id, unitcode, metadata, is_deleted, deleted_at, timestamps
    $table->foreignId('category_id')->constrained('gift_categories')->onDelete('restrict');
    $table->string('code')->unique();     // Trùng với tên thư mục trong public/templates/ (vd: birthday_cake_3d)
    $table->string('name');               // Tên hiển thị của mẫu
    $table->text('description')->nullable();
    
    // Thương mại & Thống kê
    $table->decimal('price', 12, 2)->default(0);
    $table->integer('discount')->default(0); // % giảm giá
    $table->integer('sold')->default(0);      // Lượt đã bán
    $table->integer('stars')->default(0);     // Lượt đánh giá/yêu thích
    
    // Metadata & Configs
    $table->boolean('is_hot')->default(false);
    $table->boolean('is_active')->default(true);
    $table->string('demo_url')->nullable();
    $table->string('guide_url')->nullable();
    $table->string('video_url')->nullable();
    
    // SEO Fields
    $table->string('meta_title')->nullable();
    $table->string('meta_description')->nullable();
    $table->string('meta_keywords')->nullable();
});
```

### 3. Bảng Quà tặng của khách hàng (`gifts`)
Lưu trữ thông tin quà tặng cụ thể mà người dùng tạo ra để gửi bạn bè.
```php
Schema::create('gifts', function (Blueprint $table) {
    $table->baseColumns();          // Khởi tạo id, unitcode, metadata, is_deleted, deleted_at, timestamps
    $table->uuid('uuid')->unique(); // ID ngẫu nhiên làm link xem quà (vd: ndhgift.com/g/uuid)
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('template_id')->constrained('gift_templates')->onDelete('restrict');
    
    // Thông tin người gửi/nhận
    $table->string('sender_name');
    $table->string('receiver_name');
    $table->string('title');
    $table->text('message'); // Lời chúc
    
    // Cấu hình tùy biến động (nhạc, ảnh, cài đặt 3D khác) lưu dạng JSON
    $table->json('settings'); 
    
    $table->integer('view_count')->default(0);
    $table->boolean('is_paid')->default(false);
});
```

---

## 📂 Tổ Chức Thư Mục & Cơ Chế Manifest.json

Mỗi mẫu quà tặng 3D/Interactive sẽ là một thư mục static nằm tại:
`public/templates/{template_code}/`

### Cấu trúc thư mục mẫu:
```
public/templates/birthday_cake_3d/
├── index.html          # File chạy chính của quà tặng
├── preview.png         # Ảnh chụp giao diện làm thumbnail
├── manifest.json       # Định nghĩa các cấu hình tùy biến động
└── assets/             # Chứa file 3D (.gltf), textures, js, css riêng của template
```

---

## 🚀 Lộ Trình Triển Khai Chi Tiết (Implementation Steps)

Trong turn này, chúng ta sẽ xây dựng nền móng cốt lõi cho danh mục quà tặng:

### Bước 1: Tạo Bảng Danh Mục (`gift_categories`)
- Tạo file migration `create_gift_categories_table`.
- Tạo model `GiftCategory.php` với cấu hình SoftDeletes, Casts, Fillable và Relationship.
- Tạo `GiftCategorySeeder.php` để khởi tạo các danh mục ban đầu (`birthday`, `love`, `thank`, `anniversary`...) khớp với giao diện tĩnh.

### Bước 2: Tạo Trang Quản Lý Categories Trong Admin Panel
- **Routes**: Đăng ký resource routes `admin.gift-categories` trong `routes/admin.php`.
- **Controller**: Tạo `GiftCategoryController.php` xử lý CRUD danh mục quà tặng (phân trang, tìm kiếm, bật/tắt kích hoạt, xóa).
- **Views**:
  - `gift-category-index.blade.php`: Giao diện danh sách danh mục (bảng dữ liệu, lọc, cột icon trực quan).
  - `gift-category-modals.blade.php`: Modal thêm mới/chỉnh sửa danh mục chia 2 cột hiện đại, dùng Alpine.js điều khiển.
- **Sidebar**: Cập nhật menu link thực tế cho mục "Quản lý danh mục" trong `navbar-index.blade.php`.

### Bước 3: Động Hóa Trang Client-side (`gift-index.blade.php`)
- Cập nhật hàm điều hướng trang Gift ngoài Client để truy vấn danh sách `GiftCategory` đang hoạt động (`is_active = 1`).
- Truyền danh sách danh mục động qua Blade View, loại bỏ mảng tĩnh trong Alpine.js.

---

## 🧪 Kế Hoạch Xác Minh (Verification Plan)

### Kiểm thử tự động (Automated Tests)
- Tạo file test `GiftCategoryControllerTest.php` trong thư mục `tests/Feature/Admin`:
  - Test Admin xem danh sách, lọc và tìm kiếm danh mục.
  - Test validation khi tạo danh mục (bắt buộc nhập name, slug phải là unique).
  - Test bật/tắt kích hoạt danh mục (`toggleActive`).
  - Test soft delete danh mục.
  - Test chặn người dùng thông thường/khách vãng lai truy cập trang quản trị danh mục.

### Kiểm thử thủ công (Manual Verification)
- Chạy migrate và seeder.
- Truy cập trang quà tặng ngoài client kiểm tra thanh Tabs danh mục hiển thị đầy đủ, chính xác dữ liệu từ database truyền ra.
