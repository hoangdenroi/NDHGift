<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm các cột bảo mật vào bảng users:
 * - status: trạng thái tài khoản (active/suspended/banned)
 * - is_deleted: cờ xóa mềm
 * - deleted_at: thời điểm xóa mềm
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('password')->index();
            $table->boolean('is_deleted')->default(false)->after('status')->index();
            $table->dateTime('deleted_at')->nullable()->after('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_deleted', 'deleted_at']);
        });
    }
};
