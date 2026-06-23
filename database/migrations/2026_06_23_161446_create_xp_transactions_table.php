<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration tạo bảng xp_transactions.
     */
    public function up(): void
    {
        Schema::create('xp_transactions', function (Blueprint $table) {
            // Sử dụng baseColumns của hệ thống (tự sinh id, unitcode, metadata, is_deleted, deleted_at, timestamps)
            $table->baseColumns();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount')->comment('Số lượng XP cộng (hoặc trừ nếu có)');
            $table->string('source', 50)->index()->comment('Nguồn cộng XP: register, verify_email, topup, gift_create, referral_signup, referral_first_deposit, login_streak...');
            $table->string('reference_type', 150)->nullable()->comment('Loại đối tượng liên kết (Model)');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID đối tượng liên kết');
            $table->text('description')->nullable()->comment('Mô tả chi tiết giao dịch XP');

            // Index tối ưu tìm kiếm
            $table->index(['reference_type', 'reference_id'], 'xp_transactions_reference_idx');

            // Unique index để ngăn ngừa cộng trùng XP cho cùng một hành động cụ thể
            $table->unique(['user_id', 'source', 'reference_type', 'reference_id'], 'xp_unique_action');
        });
    }

    /**
     * Hoàn tác migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('xp_transactions');
    }
};

