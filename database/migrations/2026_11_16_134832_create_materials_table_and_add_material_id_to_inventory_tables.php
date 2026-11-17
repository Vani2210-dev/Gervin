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
        // 1. Tạo bảng materials tổng quát cho mọi vật tư (đinh vít, ốc, bản lề, hóa chất, keo, nhôm kính, ván, chỉ...)
        if (!Schema::hasTable('materials')) {
            Schema::create('materials', function (Blueprint $table) {
                $table->id();
                $table->string('code')->index(); // Mã hàng / SKU
                $table->string('name'); // Tên hàng hóa / vật tư
                $table->string('warehouse_code')->nullable()->index(); // Mã kho kế toán (153_PK, 152_ACRYLIC, 152_HC...)
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->string('category')->nullable()->index(); // Tên kho / Nhóm hàng
                $table->string('unit')->default('Cái'); // ĐVT (Cái, Con, Hộp, Cuộn, Lọ, Kg, Thanh, Tấm, m, m2...)
                $table->string('item_type')->default('hardware')->index(); // board, edge_band, hardware, chemical, aluminum, glass, finished, other
                $table->string('origin')->nullable(); // Nguồn gốc / Xuất xứ
                $table->double('cost_price')->default(0); // Giá vốn
                $table->double('min_stock')->default(10); // Tồn tối thiểu
                $table->double('initial_stock')->default(0); // Tồn đầu kỳ
                $table->double('current_stock')->default(0)->index(); // Tồn kho hiện tại
                $table->foreignId('wood_board_id')->nullable()->constrained('wood_boards')->nullOnDelete();
                $table->foreignId('wood_board_price_id')->nullable()->constrained('wood_board_prices')->nullOnDelete();
                $table->string('status')->default('active')->index();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['warehouse_code', 'code']);
            });
        }

        // 2. Thêm cột material_id vào các bảng giao dịch kho nếu chưa có
        if (Schema::hasTable('inventory_receipt_items') && !Schema::hasColumn('inventory_receipt_items', 'material_id')) {
            Schema::table('inventory_receipt_items', function (Blueprint $table) {
                $table->foreignId('material_id')->nullable()->after('receipt_id')->constrained('materials')->nullOnDelete();
            });
        }

        if (Schema::hasTable('inventory_issue_items') && !Schema::hasColumn('inventory_issue_items', 'material_id')) {
            Schema::table('inventory_issue_items', function (Blueprint $table) {
                $table->foreignId('material_id')->nullable()->after('issue_id')->constrained('materials')->nullOnDelete();
            });
        }

        if (Schema::hasTable('inventory_transactions') && !Schema::hasColumn('inventory_transactions', 'material_id')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->foreignId('material_id')->nullable()->after('warehouse_id')->constrained('materials')->nullOnDelete();
            });
        }

        if (Schema::hasTable('inventory_stocktake_items') && !Schema::hasColumn('inventory_stocktake_items', 'material_id')) {
            Schema::table('inventory_stocktake_items', function (Blueprint $table) {
                $table->foreignId('material_id')->nullable()->after('stocktake_id')->constrained('materials')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_stocktake_items') && Schema::hasColumn('inventory_stocktake_items', 'material_id')) {
            Schema::table('inventory_stocktake_items', function (Blueprint $table) {
                $table->dropForeign(['material_id']);
                $table->dropColumn('material_id');
            });
        }

        if (Schema::hasTable('inventory_transactions') && Schema::hasColumn('inventory_transactions', 'material_id')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->dropForeign(['material_id']);
                $table->dropColumn('material_id');
            });
        }

        if (Schema::hasTable('inventory_issue_items') && Schema::hasColumn('inventory_issue_items', 'material_id')) {
            Schema::table('inventory_issue_items', function (Blueprint $table) {
                $table->dropForeign(['material_id']);
                $table->dropColumn('material_id');
            });
        }

        if (Schema::hasTable('inventory_receipt_items') && Schema::hasColumn('inventory_receipt_items', 'material_id')) {
            Schema::table('inventory_receipt_items', function (Blueprint $table) {
                $table->dropForeign(['material_id']);
                $table->dropColumn('material_id');
            });
        }

        Schema::dropIfExists('materials');
    }
};
