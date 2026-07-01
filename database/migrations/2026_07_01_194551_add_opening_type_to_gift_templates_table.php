<?php

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
        Schema::table('gift_templates', function (Blueprint $table) {
            $table->string('opening_type')
                ->default('auto_load')
                ->after('form_schema')
                ->comment('Kiểu mở đầu: auto_load hoặc press_hold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_templates', function (Blueprint $table) {
            $table->dropColumn('opening_type');
        });
    }
};
