<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm cột form_schema (JSON) vào gift_templates.
 *
 * Mỗi template tự định nghĩa bộ trường form cần hiển thị — frontend render form động,
 * dữ liệu user nhập được lưu vào user_gifts.content_data.
 * Khi thêm category mới chỉ cần cấu hình form_schema, không cần sửa code hay migrate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_templates', function (Blueprint $table) {
            $table->json('form_schema')
                ->nullable()
                ->after('video_url')
                ->comment('Schema JSON định nghĩa form chỉnh sửa cho template này');
        });
    }

    public function down(): void
    {
        Schema::table('gift_templates', function (Blueprint $table) {
            $table->dropColumn('form_schema');
        });
    }
};
