<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng gift_effects — Hiệu ứng add-on premium.
 *
 * Admin quản lý: tuyết rơi, pháo hoa, confetti, nhạc nền đặc biệt, khung ảnh 3D...
 * User chọn hiệu ứng trong trang editor, giá cộng vào tổng thanh toán.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_effects', function (Blueprint $table) {
            $table->baseColumns(); // id, unitcode, metadata, is_deleted, deleted_at, timestamps

            $table->string('code', 50)->unique()->comment('Mã định danh: snow_fall, fireworks, confetti...');
            $table->string('name', 100)->comment('Tên hiển thị');
            $table->text('description')->nullable()->comment('Mô tả chi tiết');
            $table->string('type', 30)->comment('Phân loại: animation, music, frame, transition, filter');
            $table->decimal('price', 12, 2)->default(0)->comment('Giá bán');
            $table->string('preview_url', 500)->nullable()->comment('URL preview trên R2 (video/gif)');
            $table->string('icon', 50)->nullable()->comment('Material icon name');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->boolean('is_active')->default(true)->comment('Đang bán hay không');

            // === INDEXES ===
            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_effects');
    }
};
