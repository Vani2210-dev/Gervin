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
        // 1. Drop product_code column from order items tables
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('acrylic_order_items', 'product_code')) {
                $table->dropColumn('product_code');
            }
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('glass_order_items', 'product_code')) {
                $table->dropColumn('product_code');
            }
        });

        Schema::table('min_late_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('min_late_order_items', 'product_code')) {
                $table->dropColumn('product_code');
            }
        });

        // 2. Create the 3 small tables for item codes
        Schema::create('acrylic_order_item_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acrylic_order_item_id');
            $table->string('product_id')->unique();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('acrylic_order_item_id', 'fk_acrylic_item_codes_item_id')
                  ->references('id')
                  ->on('acrylic_order_items')
                  ->onDelete('cascade');
        });

        Schema::create('glass_order_item_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('glass_order_item_id');
            $table->string('product_id')->unique();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('glass_order_item_id', 'fk_glass_item_codes_item_id')
                  ->references('id')
                  ->on('glass_order_items')
                  ->onDelete('cascade');
        });

        Schema::create('min_late_order_item_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('min_late_order_item_id');
            $table->string('product_id')->unique();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('min_late_order_item_id', 'fk_min_late_item_codes_item_id')
                  ->references('id')
                  ->on('min_late_order_items')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('min_late_order_item_codes');
        Schema::dropIfExists('glass_order_item_codes');
        Schema::dropIfExists('acrylic_order_item_codes');

        Schema::table('min_late_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('min_late_order_items', 'product_code')) {
                $table->string('product_code', 50)->nullable();
            }
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('glass_order_items', 'product_code')) {
                $table->string('product_code', 50)->nullable();
            }
        });

        Schema::table('acrylic_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('acrylic_order_items', 'product_code')) {
                $table->string('product_code', 50)->nullable();
            }
        });
    }
};
