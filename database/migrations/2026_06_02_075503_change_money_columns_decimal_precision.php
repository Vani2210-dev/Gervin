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
            $table->decimal('total_amount', 15, 0)->default(0)->change();
        });

        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 0)->default(0)->change();
            $table->decimal('total_price', 15, 0)->default(0)->change();
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 0)->default(0)->change();
            $table->decimal('total_price', 15, 0)->default(0)->change();
        });

        Schema::table('payment_details', function (Blueprint $table) {
            $table->decimal('price', 15, 0)->default(0)->change();
            $table->decimal('price_only', 15, 0)->default(0)->change();
            $table->decimal('total', 15, 0)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->default(0)->change();
        });

        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->default(0)->change();
            $table->decimal('total_price', 15, 2)->default(0)->change();
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->default(0)->change();
            $table->decimal('total_price', 15, 2)->default(0)->change();
        });

        Schema::table('payment_details', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->default(0)->change();
            $table->decimal('price_only', 15, 2)->default(0)->change();
            $table->decimal('total', 15, 2)->default(0)->change();
        });
    }
};
