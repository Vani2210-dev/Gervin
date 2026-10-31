<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_receipts') && !Schema::hasColumn('inventory_receipts', 'order_id')) {
            Schema::table('inventory_receipts', function (Blueprint $table) {
                $table->unsignedBigInteger('order_id')->nullable()->after('warehouse_id')->index();
            });
        }

        if (Schema::hasTable('inventory_issues') && !Schema::hasColumn('inventory_issues', 'order_id')) {
            Schema::table('inventory_issues', function (Blueprint $table) {
                $table->unsignedBigInteger('order_id')->nullable()->after('warehouse_id')->index();
            });
        }

        if (Schema::hasTable('order_supplies') && !Schema::hasColumn('order_supplies', 'warehouse_id')) {
            Schema::table('order_supplies', function (Blueprint $table) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('inventory_action');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory_receipts') && Schema::hasColumn('inventory_receipts', 'order_id')) {
            Schema::table('inventory_receipts', function (Blueprint $table) {
                $table->dropColumn('order_id');
            });
        }

        if (Schema::hasTable('inventory_issues') && Schema::hasColumn('inventory_issues', 'order_id')) {
            Schema::table('inventory_issues', function (Blueprint $table) {
                $table->dropColumn('order_id');
            });
        }

        if (Schema::hasTable('order_supplies') && Schema::hasColumn('order_supplies', 'warehouse_id')) {
            Schema::table('order_supplies', function (Blueprint $table) {
                $table->dropColumn('warehouse_id');
            });
        }
    }
};

