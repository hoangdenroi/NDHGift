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
        Schema::create('gift_templates', function (Blueprint $table) {
            $table->baseColumns(); // Khởi tạo id, unitcode, metadata, is_deleted, deleted_at, timestamps
            
            $table->foreignId('category_id')->constrained('gift_categories')->onDelete('restrict');
            $table->string('code')->unique(); // Tên thư mục chứa template trong public/templates/ (vd: heart_3d)
            $table->string('name'); // Tên hiển thị của mẫu quà tặng
            $table->text('description')->nullable();

            // Thương mại & Thống kê
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('discount')->default(0); // % giảm giá
            $table->integer('sold')->default(0); // Lượt đã bán
            $table->integer('stars')->default(0); // Lượt yêu thích/đánh giá

            // Metadata & Trạng thái
            $table->boolean('is_hot')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('demo_url')->nullable();
            $table->string('guide_url')->nullable();
            $table->string('video_url')->nullable();

            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_templates');
    }
};
