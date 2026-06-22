<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->baseColumns();

            // Khóa ngoại, tự động đánh index
            $table->unsignedBigInteger('user_id')->nullable();
            
            $table->string('scope', 20)->index()->comment('Phạm vi thông báo (user, broadcast, system)');
            $table->string('title', 255)->comment('Tiêu đề thông báo');
            $table->text('message')->nullable()->comment('Nội dung chi tiết thông báo');
            $table->string('type', 50)->nullable()->index()->comment('Loại thông báo (success, payment, order, balance...)');
            $table->string('related_entity_type', 50)->nullable()->comment('Loại thực thể liên quan (ví dụ: Transaction, User...)');
            $table->unsignedBigInteger('related_entity_id')->nullable()->comment('ID của thực thể liên quan');
            $table->boolean('is_read')->default(false)->index()->comment('Trạng thái đã đọc (1 = đã đọc, 0 = chưa đọc)');
            $table->timestamp('read_at')->nullable()->comment('Thời gian người dùng đọc thông báo');
            $table->string('priority', 20)->nullable()->comment('Mức độ ưu tiên (low, medium, high, urgent)');
            $table->text('action_url')->nullable()->comment('URL điều hướng khi người dùng nhấp vào thông báo');
            $table->json('data')->nullable()->comment('Dữ liệu đính kèm thông báo');
            $table->timestamp('expires_at')->nullable()->index()->comment('Thời gian hết hiệu lực hiển thị thông báo');

            // Đặt tên khóa ngoại tường minh để tránh xung đột trên PostgreSQL
            $table->foreign('user_id', 'fk_notifications_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
