<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm các trường hỗ trợ khóa tài khoản có lý do và timestamp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('banned_reason', 500)->nullable()->after('status')
                ->comment('Lý do khóa/tạm ngưng tài khoản');
            $table->timestamp('suspended_at')->nullable()->after('banned_reason')
                ->comment('Thời điểm tài khoản bị khóa');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['banned_reason', 'suspended_at']);
        });
    }
};
