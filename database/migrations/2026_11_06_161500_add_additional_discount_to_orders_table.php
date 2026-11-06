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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('additional_discount_percent', 5, 2)->default(0)->after('vat_amount')->comment('Chiết khấu thêm (%) trên tổng thanh toán');
            $table->double('additional_discount_amount')->default(0)->after('additional_discount_percent')->comment('Tiền chiết khấu thêm trên tổng thanh toán');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['additional_discount_percent', 'additional_discount_amount']);
        });
    }
};

