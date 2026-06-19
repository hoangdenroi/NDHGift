<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng users — Quản lý tài khoản người dùng hệ thống NDHGift.
 *
 * Thiết kế tập trung cho nền tảng tạo trang quà tặng:
 * - Thông tin cá nhân + xác thực (email, phone, Google/Facebook OAuth)
 * - Ví nội bộ (balance) để thanh toán template premium
 * - Cá nhân hóa trải nghiệm (settings json: theme, ngôn ngữ, thông báo)
 * - Bảo mật (status, soft delete, tracking login)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->ulid('unitcode')->unique();

            // === THÔNG TIN CÁ NHÂN ===
            $table->string('username')->unique()->comment('Tên đăng nhập / định danh công khai trên URL');
            $table->string('fullname')->comment('Họ tên đầy đủ hiển thị');
            $table->string('email')->unique();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('avatar_url')->nullable();

            // === XÁC THỰC & ĐĂNG NHẬP ===
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('google_id')->nullable()->unique()->comment('Google OAuth ID');
            $table->string('facebook_id')->nullable()->unique()->comment('Facebook OAuth ID');

            // === VÍ NỘI BỘ — thanh toán template premium ===
            $table->decimal('balance', 19, 4)->default(0);

            // === PHÂN QUYỀN & TRẠNG THÁI ===
            $table->string('role', 20)->default('user')->index();
            $table->string('status', 20)->default('active')->index();

            // === CÁ NHÂN HÓA & CÀI ĐẶT ===
            $table->json('settings')->nullable()->comment('Cài đặt người dùng (theme, notifications, language)');

            // === BẢO MẬT & TRACKING ===
            $table->timestamp('last_change_password_at')->nullable();
            $table->timestamp('last_login_at')->nullable();

            // === XÓA MỀM ===
            $table->boolean('is_deleted')->default(false)->index();
            $table->dateTime('deleted_at')->nullable();

            // === METADATA MỞ RỘNG ===
            $table->json('metadata')->nullable()->comment('Dữ liệu mở rộng linh hoạt');

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
