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
        // 1. inventory_receipt_items
        if (Schema::hasTable('inventory_receipt_items')) {
            Schema::table('inventory_receipt_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_receipt_items', 'height')) {
                    $table->double('height')->nullable()->after('quantity');
                }
                if (!Schema::hasColumn('inventory_receipt_items', 'width')) {
                    $table->double('width')->nullable()->after('height');
                }
            });
        }

        // 2. inventory_issue_items
        if (Schema::hasTable('inventory_issue_items')) {
            Schema::table('inventory_issue_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_issue_items', 'height')) {
                    $table->double('height')->nullable()->after('quantity');
                }
                if (!Schema::hasColumn('inventory_issue_items', 'width')) {
                    $table->double('width')->nullable()->after('height');
                }
            });
        }

        // 3. inventory_stocktake_items
        if (Schema::hasTable('inventory_stocktake_items')) {
            Schema::table('inventory_stocktake_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_stocktake_items', 'height')) {
                    $table->double('height')->nullable()->after('actual_quantity');
                }
                if (!Schema::hasColumn('inventory_stocktake_items', 'width')) {
                    $table->double('width')->nullable()->after('height');
                }
            });
        }

        // 4. inventory_transactions
        if (Schema::hasTable('inventory_transactions')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_transactions', 'height')) {
                    $table->double('height')->nullable()->after('out_qty');
                }
                if (!Schema::hasColumn('inventory_transactions', 'width')) {
                    $table->double('width')->nullable()->after('height');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'inventory_receipt_items',
            'inventory_issue_items',
            'inventory_stocktake_items',
            'inventory_transactions',
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    if (Schema::hasColumn($tbl, 'width')) {
                        $table->dropColumn('width');
                    }
                    if (Schema::hasColumn($tbl, 'height')) {
                        $table->dropColumn('height');
                    }
                });
            }
        }
    }
};
