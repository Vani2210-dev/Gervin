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
        // 1. Add inventory columns to wood_boards table
        Schema::table('wood_boards', function (Blueprint $table) {
            if (!Schema::hasColumn('wood_boards', 'origin_code')) {
                $table->string('origin_code')->nullable()->after('color_code')->index();
            }
            if (!Schema::hasColumn('wood_boards', 'unit')) {
                $table->string('unit')->default('Tấm')->after('origin_code');
            }
            if (!Schema::hasColumn('wood_boards', 'cost_price')) {
                $table->double('cost_price')->default(0)->after('unit');
            }
            if (!Schema::hasColumn('wood_boards', 'min_stock')) {
                $table->double('min_stock')->default(50)->after('cost_price');
            }
            if (!Schema::hasColumn('wood_boards', 'initial_stock')) {
                $table->double('initial_stock')->default(0)->after('min_stock');
            }
            if (!Schema::hasColumn('wood_boards', 'current_stock')) {
                $table->double('current_stock')->default(0)->after('initial_stock')->index();
            }
            if (!Schema::hasColumn('wood_boards', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('current_stock')->constrained('warehouses')->nullOnDelete();
            }
            if (!Schema::hasColumn('wood_boards', 'status')) {
                $table->string('status')->default('active')->after('warehouse_id');
            }
            if (!Schema::hasColumn('wood_boards', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });

        // 2. Sync all existing materials into wood_boards if materials table exists
        if (Schema::hasTable('materials')) {
            $materials = DB::table('materials')->get();
            foreach ($materials as $m) {
                // Find or create wood_board by color_code
                $board = DB::table('wood_boards')->where('color_code', $m->code)->first();
                if ($board) {
                    DB::table('wood_boards')->where('id', $board->id)->update([
                        'origin_code'   => $m->origin_code ?: $board->origin_code,
                        'price_group'   => $m->category ?: $board->price_group,
                        'unit'          => $m->unit ?: 'Tấm',
                        'cost_price'    => $m->cost_price ?: 0,
                        'min_stock'     => $m->min_stock ?: 50,
                        'initial_stock' => $m->initial_stock ?: 0,
                        'current_stock' => $m->current_stock ?: 0,
                        'warehouse_id'  => $m->warehouse_id,
                        'status'        => $m->status ?: 'active',
                        'notes'         => $m->notes,
                    ]);
                } else {
                    DB::table('wood_boards')->insert([
                        'color_code'    => $m->code,
                        'origin_code'   => $m->origin_code,
                        'price_group'   => $m->category ?: 'Tấm Acrylic TQ',
                        'unit'          => $m->unit ?: 'Tấm',
                        'cost_price'    => $m->cost_price ?: 0,
                        'min_stock'     => $m->min_stock ?: 50,
                        'initial_stock' => $m->initial_stock ?: 0,
                        'current_stock' => $m->current_stock ?: 0,
                        'warehouse_id'  => $m->warehouse_id,
                        'status'        => $m->status ?: 'active',
                        'notes'         => $m->notes,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        }

        // Also seed any missing boards from Warehouse sizes_config (GV01, GV120, etc.)
        $warehouse = DB::table('warehouses')->first();
        if ($warehouse && !empty($warehouse->sizes_config)) {
            $config = json_decode($warehouse->sizes_config, true) ?: [];
            foreach ($config as $group) {
                $groupName = $group['name'] ?? 'Tấm Acrylic TQ';
                $sizes = $group['sizes'] ?? [];
                foreach ($sizes as $s) {
                    $colorCode = trim($s['name'] ?? '');
                    $originCode = trim($s['code'] ?? '');
                    if ($colorCode) {
                        $existing = DB::table('wood_boards')->where('color_code', $colorCode)->first();
                        if (!$existing) {
                            DB::table('wood_boards')->insert([
                                'color_code'    => $colorCode,
                                'origin_code'   => $originCode ?: null,
                                'price_group'   => $groupName,
                                'unit'          => 'Tấm',
                                'cost_price'    => 0,
                                'min_stock'     => 50,
                                'initial_stock' => 0,
                                'current_stock' => 0,
                                'warehouse_id'  => $warehouse->id,
                                'status'        => 'active',
                                'notes'         => $s['description'] ?? null,
                                'created_at'    => now(),
                                'updated_at'    => now(),
                            ]);
                        } elseif (empty($existing->origin_code) && $originCode) {
                            DB::table('wood_boards')->where('id', $existing->id)->update([
                                'origin_code' => $originCode,
                            ]);
                        }
                    }
                }
            }
        }

        // 3. Update inventory_receipt_items: add wood_board_id, migrate data, drop material_id
        if (Schema::hasTable('inventory_receipt_items')) {
            Schema::table('inventory_receipt_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_receipt_items', 'wood_board_id')) {
                    $table->foreignId('wood_board_id')->nullable()->after('receipt_id')->constrained('wood_boards')->cascadeOnDelete();
                }
            });

            if (Schema::hasTable('materials') && Schema::hasColumn('inventory_receipt_items', 'material_id')) {
                // Copy link: material.code -> wood_board.color_code
                DB::statement("
                    UPDATE inventory_receipt_items iri
                    JOIN materials m ON iri.material_id = m.id
                    JOIN wood_boards wb ON wb.color_code = m.code
                    SET iri.wood_board_id = wb.id
                ");

                Schema::table('inventory_receipt_items', function (Blueprint $table) {
                    $table->dropForeign(['material_id']);
                    $table->dropColumn('material_id');
                });
            }
        }

        // 4. Update inventory_issue_items
        if (Schema::hasTable('inventory_issue_items')) {
            Schema::table('inventory_issue_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_issue_items', 'wood_board_id')) {
                    $table->foreignId('wood_board_id')->nullable()->after('issue_id')->constrained('wood_boards')->cascadeOnDelete();
                }
            });

            if (Schema::hasTable('materials') && Schema::hasColumn('inventory_issue_items', 'material_id')) {
                DB::statement("
                    UPDATE inventory_issue_items iii
                    JOIN materials m ON iii.material_id = m.id
                    JOIN wood_boards wb ON wb.color_code = m.code
                    SET iii.wood_board_id = wb.id
                ");

                Schema::table('inventory_issue_items', function (Blueprint $table) {
                    $table->dropForeign(['material_id']);
                    $table->dropColumn('material_id');
                });
            }
        }

        // 5. Update inventory_stocktake_items
        if (Schema::hasTable('inventory_stocktake_items')) {
            Schema::table('inventory_stocktake_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_stocktake_items', 'wood_board_id')) {
                    $table->foreignId('wood_board_id')->nullable()->after('stocktake_id')->constrained('wood_boards')->cascadeOnDelete();
                }
            });

            if (Schema::hasTable('materials') && Schema::hasColumn('inventory_stocktake_items', 'material_id')) {
                DB::statement("
                    UPDATE inventory_stocktake_items isi
                    JOIN materials m ON isi.material_id = m.id
                    JOIN wood_boards wb ON wb.color_code = m.code
                    SET isi.wood_board_id = wb.id
                ");

                Schema::table('inventory_stocktake_items', function (Blueprint $table) {
                    $table->dropForeign(['material_id']);
                    $table->dropColumn('material_id');
                });
            }
        }

        // 6. Update inventory_transactions
        if (Schema::hasTable('inventory_transactions')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_transactions', 'wood_board_id')) {
                    $table->foreignId('wood_board_id')->nullable()->after('warehouse_id')->constrained('wood_boards')->cascadeOnDelete();
                }
            });

            if (Schema::hasTable('materials') && Schema::hasColumn('inventory_transactions', 'material_id')) {
                DB::statement("
                    UPDATE inventory_transactions it
                    JOIN materials m ON it.material_id = m.id
                    JOIN wood_boards wb ON wb.color_code = m.code
                    SET it.wood_board_id = wb.id
                ");

                Schema::table('inventory_transactions', function (Blueprint $table) {
                    $table->dropForeign(['material_id']);
                    $table->dropColumn('material_id');
                });
            }
        }

        // 7. Drop materials table
        Schema::dropIfExists('materials');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed as materials is permanently replaced by wood_boards
    }
};
