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
        Schema::table('user_levels', function (Blueprint $table) {
            $table->unsignedInteger('checkin_streak')->default(0)->after('tier')->comment('Chuỗi ngày điểm danh liên tiếp');
            $table->timestamp('last_checked_in_at')->nullable()->after('checkin_streak')->comment('Thời điểm điểm danh gần nhất');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->dropColumn(['checkin_streak', 'last_checked_in_at']);
        });
    }
};
