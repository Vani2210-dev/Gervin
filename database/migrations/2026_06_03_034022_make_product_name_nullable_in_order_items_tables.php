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
            $table->string('product_name')->nullable()->change();
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->change();
        });

        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable(false)->change();
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable(false)->change();
        });

        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }
};
