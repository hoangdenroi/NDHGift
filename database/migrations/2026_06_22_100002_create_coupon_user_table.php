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
        Schema::create('coupon_user', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('coupon_id');
            
            $table->timestamp('used_at')->useCurrent()->index();

            // Đặt tên khóa ngoại tường minh để tránh xung đột trên PostgreSQL
            $table->foreign('user_id', 'fk_coupon_user_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
                
            $table->foreign('coupon_id', 'fk_coupon_user_coupon_id')
                ->references('id')
                ->on('coupons')
                ->cascadeOnDelete();

            $table->index('user_id');
            $table->index('coupon_id');

            // Đảm bảo mỗi người dùng chỉ dùng mỗi coupon tối đa 1 lần ở mức database layer
            $table->unique(['user_id', 'coupon_id'], 'uq_coupon_user_user_coupon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_user');
    }
};
