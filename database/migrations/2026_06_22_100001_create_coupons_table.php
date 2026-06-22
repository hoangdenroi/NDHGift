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
        Schema::create('coupons', function (Blueprint $table) {
            $table->baseColumns();

            $table->string('code', 50)->unique()->index()->comment('Mã giảm giá/quà tặng');
            $table->enum('type', ['percent', 'fixed'])->default('fixed')->comment('Loại coupon: percent (phần trăm) hoặc fixed (số tiền cố định)');
            $table->decimal('value', 10, 2)->comment('Giá trị của coupon');
            $table->decimal('max_discount', 10, 2)->nullable()->comment('Số tiền giảm tối đa (chỉ áp dụng cho loại percent)');
            $table->decimal('min_order', 10, 2)->default(0)->comment('Giá trị đơn hàng tối thiểu để áp dụng mã');
            $table->integer('max_uses')->nullable()->comment('Số lần sử dụng tối đa trên toàn hệ thống (null = không giới hạn)');
            $table->integer('used_count')->default(0)->comment('Số lần đã được sử dụng thực tế');
            $table->timestamp('starts_at')->nullable()->index()->comment('Thời gian bắt đầu có hiệu lực');
            $table->timestamp('expires_at')->nullable()->index()->comment('Thời gian hết hiệu lực');
            $table->boolean('is_active')->default(true)->index()->comment('Trạng thái kích hoạt (1 = hoạt động, 0 = khóa)');
            $table->enum('status', ['public', 'private'])->default('public')->index()->comment('Phạm vi hiển thị: public (công khai cho mọi người), private (mã quà tặng riêng)');

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
