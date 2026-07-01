<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng gift_reactions — Cảm xúc từ người xem trang quà.
 *
 * Không cần đăng nhập — dùng visitor_hash (IP + UA) để định danh.
 * Chống spam: rate limit 10 req/phút/IP + Captcha Turnstile + unique constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_gift_id')->constrained('user_gifts')->onDelete('cascade');
            $table->string('reaction_type', 20)->comment('heart, cry, surprise, laugh, love');
            $table->string('visitor_hash', 64)->comment('Hash(IP + UA) định danh visitor chống trùng');
            $table->string('ip_address', 45)->nullable()->comment('IP gốc để phục vụ rate limit');
            $table->timestamp('created_at')->nullable();

            // Mỗi visitor chỉ react 1 lần cho mỗi quà
            $table->unique(['user_gift_id', 'visitor_hash'], 'uq_gift_reaction_visitor');
            // Đếm nhanh tổng reaction theo quà
            $table->index('user_gift_id');
            // Phục vụ rate limit theo IP
            $table->index(['ip_address', 'created_at'], 'idx_reaction_rate_limit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_reactions');
    }
};
