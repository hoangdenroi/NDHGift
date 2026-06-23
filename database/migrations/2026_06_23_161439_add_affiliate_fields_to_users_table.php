<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration thêm các cột affiliate vào bảng users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('affiliate_code', 20)->nullable()->unique()->after('status')->comment('Mã affiliate cá nhân dùng để chia sẻ');
            $table->unsignedBigInteger('referred_by')->nullable()->after('affiliate_code')->comment('ID người dùng giới thiệu tài khoản này');

            // Thiết lập khóa ngoại liên kết với bảng users
            $table->foreign('referred_by', 'fk_users_referred_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Hoàn tác migration.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('fk_users_referred_by');
            $table->dropColumn(['affiliate_code', 'referred_by']);
        });
    }
};

