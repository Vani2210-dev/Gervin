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
        // 1. Thêm các cột quản lý Tồn kho Chỉ dán viền vào bảng wood_boards
        Schema::table('wood_boards', function (Blueprint $table) {
            if (!Schema::hasColumn('wood_boards', 'edge_band_unit')) {
                $table->string('edge_band_unit')->default('m')->after('notes');
            }
            if (!Schema::hasColumn('wood_boards', 'edge_band_current_stock')) {
                $table->double('edge_band_current_stock')->default(0)->after('edge_band_unit');
            }
            if (!Schema::hasColumn('wood_boards', 'edge_band_min_stock')) {
                $table->double('edge_band_min_stock')->default(100)->after('edge_band_current_stock');
            }
            if (!Schema::hasColumn('wood_boards', 'edge_band_cost_price')) {
                $table->double('edge_band_cost_price')->default(0)->after('edge_band_min_stock');
            }
            if (!Schema::hasColumn('wood_boards', 'edge_band_initial_stock')) {
                $table->double('edge_band_initial_stock')->default(0)->after('edge_band_cost_price');
            }
        });

        // 2. Thêm item_type vào các bảng giao dịch kho ('board' hoặc 'edge_band')
        if (Schema::hasTable('inventory_transactions') && !Schema::hasColumn('inventory_transactions', 'item_type')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->string('item_type')->default('board')->after('wood_board_price_id')->index();
            });
        }

        if (Schema::hasTable('inventory_receipt_items') && !Schema::hasColumn('inventory_receipt_items', 'item_type')) {
            Schema::table('inventory_receipt_items', function (Blueprint $table) {
                $table->string('item_type')->default('board')->after('wood_board_price_id');
            });
        }

        if (Schema::hasTable('inventory_issue_items') && !Schema::hasColumn('inventory_issue_items', 'item_type')) {
            Schema::table('inventory_issue_items', function (Blueprint $table) {
                $table->string('item_type')->default('board')->after('wood_board_price_id');
            });
        }

        if (Schema::hasTable('inventory_stocktake_items') && !Schema::hasColumn('inventory_stocktake_items', 'item_type')) {
            Schema::table('inventory_stocktake_items', function (Blueprint $table) {
                $table->string('item_type')->default('board')->after('wood_board_price_id');
            });
        }

        // 3. Khởi tạo / Đảm bảo 2 Kho cơ bản: Kho Tấm Ván & Kho Dán Chỉ
        if (Schema::hasTable('warehouses')) {
            // Kho Tấm Ván
            $boardWh = DB::table('warehouses')->where('code', 'KHO-VAN')->first();
            if (!$boardWh) {
                $existingWh = DB::table('warehouses')->first();
                if ($existingWh && empty($existingWh->code)) {
                    DB::table('warehouses')->where('id', $existingWh->id)->update([
                        'code'        => 'KHO-VAN',
                        'name'        => 'Kho Tấm Ván',
                        'item_name'   => 'Tấm ván',
                        'description' => 'Kho lưu trữ và theo dõi các mã tấm ván gỗ công nghiệp & Acrylic',
                        'status'      => 'active',
                    ]);
                } else {
                    DB::table('warehouses')->insert([
                        'code'        => 'KHO-VAN',
                        'name'        => 'Kho Tấm Ván',
                        'item_name'   => 'Tấm ván',
                        'description' => 'Kho lưu trữ và theo dõi các mã tấm ván gỗ công nghiệp & Acrylic',
                        'status'      => 'active',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            // Kho Dán Chỉ
            $edgeWh = DB::table('warehouses')->where('code', 'KHO-CHI')->first();
            if (!$edgeWh) {
                DB::table('warehouses')->insert([
                    'code'        => 'KHO-CHI',
                    'name'        => 'Kho Dán Chỉ',
                    'item_name'   => 'Chỉ dán viền',
                    'description' => 'Kho lưu trữ và theo dõi xuất nhập tồn cuộn chỉ / nẹp chỉ dán cạnh cho từng mã tấm',
                    'status'      => 'active',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wood_boards', function (Blueprint $table) {
            $table->dropColumn([
                'edge_band_unit',
                'edge_band_current_stock',
                'edge_band_min_stock',
                'edge_band_cost_price',
                'edge_band_initial_stock',
            ]);
        });

        if (Schema::hasTable('inventory_transactions') && Schema::hasColumn('inventory_transactions', 'item_type')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->dropColumn('item_type');
            });
        }
        if (Schema::hasTable('inventory_receipt_items') && Schema::hasColumn('inventory_receipt_items', 'item_type')) {
            Schema::table('inventory_receipt_items', function (Blueprint $table) {
                $table->dropColumn('item_type');
            });
        }
        if (Schema::hasTable('inventory_issue_items') && Schema::hasColumn('inventory_issue_items', 'item_type')) {
            Schema::table('inventory_issue_items', function (Blueprint $table) {
                $table->dropColumn('item_type');
            });
        }
        if (Schema::hasTable('inventory_stocktake_items') && Schema::hasColumn('inventory_stocktake_items', 'item_type')) {
            Schema::table('inventory_stocktake_items', function (Blueprint $table) {
                $table->dropColumn('item_type');
            });
        }
    }
};
