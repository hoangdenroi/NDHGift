<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration để thêm composite index cho bảng xp_transactions.
     */
    public function up(): void
    {
        Schema::table('xp_transactions', function (Blueprint $table) {
            // Thêm composite index tối ưu cho việc query lịch sử nhận XP theo user (sắp xếp theo created_at desc, id desc)
            $table->index(['user_id', 'created_at', 'id'], 'xp_transactions_user_created_id_idx');
        });
    }

    /**
     * Hoàn tác migration.
     */
    public function down(): void
    {
        Schema::table('xp_transactions', function (Blueprint $table) {
            $table->dropIndex('xp_transactions_user_created_id_idx');
        });
    }
};
