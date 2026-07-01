<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng user_gifts — Bảng cốt lõi lưu quà tặng user đã tạo.
 *
 * Luồng: chọn template → chỉnh sửa nội dung (content_data JSON linh hoạt) →
 * chọn hiệu ứng → thanh toán → nhận link công khai.
 * Trạng thái: draft → paid → expired / disabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_gifts', function (Blueprint $table) {
            $table->baseColumns(); // id, unitcode, metadata, is_deleted, deleted_at, timestamps

            // === QUAN HỆ ===
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('gift_templates')->onDelete('restrict');
            $table->foreignId('duration_plan_id')->constrained('gift_duration_plans')->onDelete('restrict');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->onDelete('set null');

            // === ĐỊNH DANH URL ===
            $table->string('slug', 80)->unique()->comment('Link công khai: /g/{slug} — mặc định 12 ký tự random');
            $table->boolean('is_custom_slug')->default(false)->comment('true = user mua gói tùy chỉnh slug');

            // === NỘI DUNG LINH HOẠT (Schema-Driven) ===
            // Dữ liệu user nhập theo form_schema của gift_templates — linh hoạt theo từng category
            $table->json('content_data')->nullable()->comment('Dữ liệu user đã nhập theo form_schema của template');

            // === THANH TOÁN ===
            $table->string('status', 20)->default('draft')->comment('draft / paid / expired / disabled');
            $table->decimal('base_price', 12, 2)->default(0)->comment('Giá template tại thời điểm tạo (snapshot)');
            $table->decimal('duration_price', 12, 2)->default(0)->comment('Giá gói thời hạn (snapshot)');
            $table->decimal('effects_price', 12, 2)->default(0)->comment('Tổng giá hiệu ứng add-on');
            $table->decimal('addons_price', 12, 2)->default(0)->comment('Giá add-on khác (custom slug...)');
            $table->decimal('discount_amount', 12, 2)->default(0)->comment('Tiền giảm từ coupon');
            $table->decimal('total_paid', 12, 2)->default(0)->comment('Tổng thực trả = base + duration + effects + addons - discount');
            $table->dateTime('paid_at')->nullable()->comment('Thời điểm thanh toán thành công');

            // === HẸN GIỜ GỬI QUÀ ===
            $table->dateTime('scheduled_at')->nullable()->comment('Thời điểm mở khóa link (null = mở ngay khi paid)');
            $table->string('timezone', 50)->default('Asia/Ho_Chi_Minh')->comment('Múi giờ người gửi');

            // === LƯỢT XEM & TRACKING ===
            $table->unsignedInteger('view_count')->default(0)->comment('Tổng lượt xem');
            $table->dateTime('last_viewed_at')->nullable()->comment('Lần xem gần nhất');
            $table->boolean('is_view_notification_enabled')->default(true)->comment('Bật/tắt thông báo khi có người xem');

            // === HẾT HẠN ===
            $table->dateTime('expires_at')->nullable()->comment('paid_at + duration_days. null = vĩnh viễn');

            // === INDEXES ===
            $table->index('status');
            $table->index('scheduled_at');
            $table->index('expires_at');
            $table->index(['user_id', 'status']); // Query quà theo user + trạng thái
            $table->index(['slug', 'status']); // Tra cứu link công khai
            $table->index(['status', 'scheduled_at']); // Job mở khóa hẹn giờ
            $table->index(['status', 'expires_at']); // Job đánh dấu hết hạn
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_gifts');
    }
};
