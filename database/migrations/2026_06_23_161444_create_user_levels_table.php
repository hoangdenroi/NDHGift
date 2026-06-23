<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration tạo bảng user_levels.
     */
    public function up(): void
    {
        Schema::create('user_levels', function (Blueprint $table) {
            // Sử dụng baseColumns của hệ thống (tự sinh id, unitcode, metadata, is_deleted, deleted_at, timestamps)
            $table->baseColumns();

            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('total_xp')->default(0)->index()->comment('Tổng số XP tích lũy');
            $table->string('tier', 20)->default('bronze')->index()->comment('Cấp bậc hiện tại của người dùng (bronze, silver, gold, platinum, diamond)');
            $table->boolean('is_frozen')->default(false)->index()->comment('Trạng thái đóng băng cấp độ nếu quá 60 ngày không hoạt động');
            $table->timestamp('last_xp_earned_at')->useCurrent()->index()->comment('Thời điểm cuối cùng nhận XP để tính thời hạn 60 ngày decay');
            $table->timestamp('tier_achieved_at')->useCurrent()->comment('Thời điểm đạt được cấp độ hiện tại');
        });
    }

    /**
     * Hoàn tác migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};

