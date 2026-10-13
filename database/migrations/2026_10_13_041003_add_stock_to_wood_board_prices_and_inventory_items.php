<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add stock to wood_board_prices
        if (Schema::hasTable('wood_board_prices')) {
            Schema::table('wood_board_prices', function (Blueprint $table) {
                if (!Schema::hasColumn('wood_board_prices', 'stock')) {
                    $table->double('stock')->default(0)->after('price_m2')->index();
                }
            });
        }

        // 2. Add wood_board_price_id to inventory_receipt_items
        if (Schema::hasTable('inventory_receipt_items')) {
            Schema::table('inventory_receipt_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_receipt_items', 'wood_board_price_id')) {
                    $table->foreignId('wood_board_price_id')->nullable()->after('wood_board_id')->constrained('wood_board_prices')->nullOnDelete();
                }
            });
        }

        // 3. Add wood_board_price_id to inventory_issue_items
        if (Schema::hasTable('inventory_issue_items')) {
            Schema::table('inventory_issue_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_issue_items', 'wood_board_price_id')) {
                    $table->foreignId('wood_board_price_id')->nullable()->after('wood_board_id')->constrained('wood_board_prices')->nullOnDelete();
                }
            });
        }

        // 4. Add wood_board_price_id to inventory_stocktake_items
        if (Schema::hasTable('inventory_stocktake_items')) {
            Schema::table('inventory_stocktake_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_stocktake_items', 'wood_board_price_id')) {
                    $table->foreignId('wood_board_price_id')->nullable()->after('wood_board_id')->constrained('wood_board_prices')->nullOnDelete();
                }
            });
        }

        // 5. Add wood_board_price_id to inventory_transactions
        if (Schema::hasTable('inventory_transactions')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_transactions', 'wood_board_price_id')) {
                    $table->foreignId('wood_board_price_id')->nullable()->after('wood_board_id')->constrained('wood_board_prices')->nullOnDelete();
                }
            });
        }

        // 6. Ensure existing wood_boards have prices and sync existing stock
        $boardTypes = DB::table('wood_board_types')->orderBy('display_order')->get();
        if ($boardTypes->isNotEmpty()) {
            $boards = DB::table('wood_boards')->get();
            foreach ($boards as $board) {
                foreach ($boardTypes as $type) {
                    $price = DB::table('wood_board_prices')
                        ->where('wood_board_id', $board->id)
                        ->where('wood_board_type_id', $type->id)
                        ->first();

                    if (!$price) {
                        $code = $board->color_code . ($type->prefix ?? '');
                        DB::table('wood_board_prices')->insert([
                            'wood_board_id'      => $board->id,
                            'wood_board_type_id' => $type->id,
                            'code'               => $code,
                            'name'               => $type->name,
                            'thickness'          => '17mm',
                            'price_board'        => 0,
                            'price_m2'           => 0,
                            'stock'              => ($type->id == $boardTypes->first()->id && $board->current_stock > 0) ? $board->current_stock : 0,
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ]);
                    } elseif ($type->id == $boardTypes->first()->id && $price->stock == 0 && $board->current_stock > 0) {
                        DB::table('wood_board_prices')->where('id', $price->id)->update([
                            'stock' => $board->current_stock,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_transactions') && Schema::hasColumn('inventory_transactions', 'wood_board_price_id')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->dropForeign(['wood_board_price_id']);
                $table->dropColumn('wood_board_price_id');
            });
        }

        if (Schema::hasTable('inventory_stocktake_items') && Schema::hasColumn('inventory_stocktake_items', 'wood_board_price_id')) {
            Schema::table('inventory_stocktake_items', function (Blueprint $table) {
                $table->dropForeign(['wood_board_price_id']);
                $table->dropColumn('wood_board_price_id');
            });
        }

        if (Schema::hasTable('inventory_issue_items') && Schema::hasColumn('inventory_issue_items', 'wood_board_price_id')) {
            Schema::table('inventory_issue_items', function (Blueprint $table) {
                $table->dropForeign(['wood_board_price_id']);
                $table->dropColumn('wood_board_price_id');
            });
        }

        if (Schema::hasTable('inventory_receipt_items') && Schema::hasColumn('inventory_receipt_items', 'wood_board_price_id')) {
            Schema::table('inventory_receipt_items', function (Blueprint $table) {
                $table->dropForeign(['wood_board_price_id']);
                $table->dropColumn('wood_board_price_id');
            });
        }

        if (Schema::hasTable('wood_board_prices') && Schema::hasColumn('wood_board_prices', 'stock')) {
            Schema::table('wood_board_prices', function (Blueprint $table) {
                $table->dropColumn('stock');
            });
        }
    }
};
