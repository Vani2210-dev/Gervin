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
        Schema::create('customer_daily_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('opening_debt', 15, 2)->default(0)->comment('Công nợ cũ đầu ngày');
            $table->decimal('order_amount', 15, 2)->default(0)->comment('Tổng tiền đơn phát sinh trong ngày');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Đã thanh toán trong ngày');
            $table->decimal('closing_debt', 15, 2)->default(0)->comment('Tổng nợ cuối ngày');
            $table->unsignedInteger('orders_count')->default(0)->comment('Số lượng đơn trong ngày');
            $table->unsignedInteger('payments_count')->default(0)->comment('Số lần thanh toán trong ngày');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'date'], 'cust_daily_debt_unique');
            $table->index(['customer_id', 'date'], 'cust_daily_debt_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_daily_debts');
    }
};

