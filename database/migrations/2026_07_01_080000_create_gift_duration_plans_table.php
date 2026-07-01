<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng gift_duration_plans — Gói thời hạn quà tặng.
 *
 * Admin cấu hình các gói: 15 ngày (free), 30 ngày, 90 ngày, Vĩnh viễn.
 * Mỗi gói có giá riêng, cộng vào tổng thanh toán khi user chọn.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_duration_plans', function (Blueprint $table) {
            $table->baseColumns(); // id, unitcode, metadata, is_deleted, deleted_at, timestamps

            $table->string('name', 50)->comment('Tên hiển thị: 15 ngày, 30 ngày, 90 ngày, Vĩnh viễn');
            $table->string('code', 30)->unique()->comment('Mã định danh: 15d, 30d, 90d, forever');
            $table->unsignedSmallInteger('duration_days')->nullable()->comment('Số ngày hiệu lực. null = vĩnh viễn');
            $table->decimal('price', 12, 2)->default(0)->comment('Giá bán gói thời hạn');
            $table->string('description', 255)->nullable()->comment('Mô tả ngắn');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->boolean('is_active')->default(true)->index()->comment('Đang bán hay không');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_duration_plans');
    }
};
