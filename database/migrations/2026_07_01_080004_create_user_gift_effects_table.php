<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng pivot user_gift_effects — Liên kết hiệu ứng đã chọn với từng quà tặng.
 *
 * Snapshot giá tại thời điểm mua để chống thay đổi giá sau này.
 * Mỗi quà không thể chọn trùng cùng 1 hiệu ứng (unique constraint).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_gift_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_gift_id')->constrained('user_gifts')->onDelete('cascade');
            $table->foreignId('gift_effect_id')->constrained('gift_effects')->onDelete('restrict');
            $table->decimal('price_at_purchase', 12, 2)->default(0)->comment('Giá hiệu ứng tại thời điểm mua (snapshot)');
            $table->json('config')->nullable()->comment('Cấu hình riêng: cường độ, tốc độ, màu sắc...');
            $table->timestamp('created_at')->nullable();

            // Không chọn trùng hiệu ứng cho cùng 1 quà
            $table->unique(['user_gift_id', 'gift_effect_id'], 'uq_user_gift_effect');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_gift_effects');
    }
};
