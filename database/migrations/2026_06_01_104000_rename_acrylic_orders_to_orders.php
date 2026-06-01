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
        // 1. Drop foreign key constraint on acrylic_order_items table
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->dropForeign(['acrylic_order_id']);
        });

        // 2. Rename the acrylic_orders table to orders
        Schema::rename('acrylic_orders', 'orders');

        // 3. Rename column acrylic_order_id to order_id in acrylic_order_items table
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->renameColumn('acrylic_order_id', 'order_id');
        });

        // 4. Re-create foreign key constraint on acrylic_order_items table referencing orders table
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop foreign key constraint on acrylic_order_items table
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        // 2. Rename column order_id to acrylic_order_id in acrylic_order_items table
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->renameColumn('order_id', 'acrylic_order_id');
        });

        // 3. Rename the orders table to acrylic_orders
        Schema::rename('orders', 'acrylic_orders');

        // 4. Re-create old foreign key constraint referencing acrylic_orders table
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->foreign('acrylic_order_id')->references('id')->on('acrylic_orders')->onDelete('cascade');
        });
    }
};
