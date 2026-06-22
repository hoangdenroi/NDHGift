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
        Schema::create('transactions', function (Blueprint $table) {
            $table->baseColumns();

            // Khóa ngoại liên kết với users
            $table->unsignedBigInteger('user_id')->nullable();
            
            $table->string('user_identifier', 255)->nullable()->comment('Định danh người dùng vãng lai (email/phone)');
            $table->unsignedBigInteger('amount')->comment('Số tiền giao dịch gốc');
            $table->unsignedBigInteger('fee')->default(0)->comment('Phí giao dịch (từ payment gateway)');
            $table->unsignedBigInteger('net_amount')->nullable()->comment('Số tiền thực nhận sau phí');
            $table->string('currency', 3)->default('VND')->comment('Loại tiền tệ');

            // Định danh giao dịch và các index quan trọng
            $table->string('transaction_no', 100)->unique()->comment('Mã giao dịch hệ thống');
            $table->string('gateway_transaction_id', 255)->nullable()->index()->comment('Transaction ID từ cổng thanh toán');
            $table->string('bank_code', 50)->nullable()->comment('Mã ngân hàng xử lý');
            $table->string('status', 30)->default('PENDING')->index()->comment('Trạng thái giao dịch (PENDING, SUCCESS, FAILED)');
            $table->string('payment_method', 30)->comment('Phương thức thanh toán (SEPAY, VNPAY, PAYPAL, COUPON...)');
            $table->string('response_code', 20)->nullable()->comment('Response code phản hồi từ gateway');
            $table->text('order_info')->nullable()->comment('Thông tin đơn hàng / Nội dung chuyển khoản');
            $table->timestamp('pay_date')->nullable()->index()->comment('Thời gian thanh toán thành công');
            $table->string('account_number', 50)->nullable()->comment('Số tài khoản chuyển khoản (nếu có)');

            // Hoàn tiền (Refund)
            $table->text('failure_reason')->nullable()->comment('Lý do giao dịch thất bại');
            $table->unsignedBigInteger('refunded_amount')->default(0)->comment('Số tiền đã hoàn trả');
            $table->timestamp('refunded_at')->nullable()->comment('Thời gian hoàn trả tiền');
            $table->text('refund_reason')->nullable()->comment('Lý do hoàn trả tiền');

            // Hết hạn giao dịch
            $table->timestamp('expires_at')->nullable()->comment('Thời gian hết hạn thanh toán');

            // Thiết lập khóa ngoại tường minh
            $table->foreign('user_id', 'fk_transactions_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
