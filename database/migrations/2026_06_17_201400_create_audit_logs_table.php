<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng audit_logs — ghi lại mọi thao tác nhạy cảm trong hệ thống
 * (login, logout, thay đổi dữ liệu, giao dịch, v.v.)
 * Sử dụng Prunable trait để tự xóa bản ghi cũ > 90 ngày.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action')->index();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Index composite cho truy vấn theo model cụ thể
            $table->index(['model_type', 'model_id']);
            // Index cho truy vấn theo thời gian (Prunable + báo cáo)
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
