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
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->text('product_name')->nullable()->change();
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->text('product_name')->nullable()->change();
        });

        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->text('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->string('product_name', 255)->nullable()->change();
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->string('product_name', 255)->nullable()->change();
        });

        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->string('name', 255)->nullable()->change();
        });
    }
};
