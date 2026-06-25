<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm cột payment_code — mã thanh toán ngắn 8 ký tự duy nhất,
     * dùng trong nội dung chuyển khoản để khớp giao dịch PENDING từ webhook.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_code', 20)
                ->nullable()
                ->unique()
                ->after('payment_method')
                ->comment('Mã thanh toán ngắn 8 ký tự, dùng trong nội dung CK để khớp giao dịch');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_code');
        });
    }
};
