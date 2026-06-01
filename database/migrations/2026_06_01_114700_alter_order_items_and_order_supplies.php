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
        // 1. Add type column to order_supplies table if not exists
        if (!Schema::hasColumn('order_supplies', 'type')) {
            Schema::table('order_supplies', function (Blueprint $table) {
                $table->enum('type', ['acrylic', 'min_late', 'glass'])->after('order_id')->nullable();
            });
        }

        // 2. Rename order_id to order_supply_id in acrylic_order_items if order_id exists
        if (Schema::hasColumn('acrylic_order_items', 'order_id')) {
            Schema::table('acrylic_order_items', function (Blueprint $table) {
                try {
                    $table->dropForeign(['order_id']);
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
            });

            Schema::table('acrylic_order_items', function (Blueprint $table) {
                $table->renameColumn('order_id', 'order_supply_id');
            });

            Schema::table('acrylic_order_items', function (Blueprint $table) {
                $table->foreign('order_supply_id')->references('id')->on('order_supplies')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('acrylic_order_items', 'order_supply_id')) {
            Schema::table('acrylic_order_items', function (Blueprint $table) {
                try {
                    $table->dropForeign(['order_supply_id']);
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
            });

            Schema::table('acrylic_order_items', function (Blueprint $table) {
                $table->renameColumn('order_supply_id', 'order_id');
            });

            Schema::table('acrylic_order_items', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }

        if (Schema::hasColumn('order_supplies', 'type')) {
            Schema::table('order_supplies', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
