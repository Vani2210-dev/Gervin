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
        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->double('straight_paste_length')->default(0)->change();
            $table->double('beveled_length')->default(0)->change();
            $table->double('vat_moi_length')->default(0)->change();
            $table->double('ban_rong_40_59')->default(0)->change();
            $table->double('ban_rong_17_39')->default(0)->change();
            $table->double('ban_rong_25_35')->default(0)->change();
            $table->double('beveled_handle')->default(0)->change();
        });

        Schema::table('payment_details', function (Blueprint $table) {
            $table->double('quantity')->default(0)->change();
            $table->double('price')->default(0)->change();
            $table->double('price_only')->default(0)->change();
            $table->double('total')->default(0)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->double('total_amount')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->decimal('straight_paste_length', 10, 2)->default(0)->change();
            $table->decimal('beveled_length', 10, 2)->default(0)->change();
            $table->decimal('vat_moi_length', 10, 2)->default(0)->change();
            $table->decimal('ban_rong_40_59', 10, 2)->default(0)->change();
            $table->decimal('ban_rong_17_39', 10, 2)->default(0)->change();
            $table->decimal('ban_rong_25_35', 10, 2)->default(0)->change();
            $table->decimal('beveled_handle', 10, 2)->default(0)->change();
        });

        Schema::table('payment_details', function (Blueprint $table) {
            $table->decimal('quantity', 15, 2)->default(0)->change();
            $table->decimal('price', 15, 2)->default(0)->change();
            $table->decimal('price_only', 15, 2)->default(0)->change();
            $table->decimal('total', 15, 2)->default(0)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->default(0)->change();
        });
    }
};
