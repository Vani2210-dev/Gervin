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
        // 1. Ensure warehouses has standard fields
        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouses', 'code')) {
                    $table->string('code')->nullable()->after('name');
                }
                if (!Schema::hasColumn('warehouses', 'address')) {
                    $table->string('address')->nullable()->after('code');
                }
                if (!Schema::hasColumn('warehouses', 'manager')) {
                    $table->string('manager')->nullable()->after('address');
                }
                if (!Schema::hasColumn('warehouses', 'status')) {
                    $table->string('status')->default('active')->after('manager');
                }
                if (!Schema::hasColumn('warehouses', 'description')) {
                    $table->text('description')->nullable()->after('status');
                }
            });
        }

        // 2. Materials (Danh mục Vật tư / Hàng hóa)
        if (!Schema::hasTable('materials')) {
            Schema::create('materials', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->index(); // SKU / Mã vật tư (GV120, GV107**...)
                $table->string('name'); // Tên vật tư
                $table->string('origin_code')->nullable()->index(); // Mã xuất xứ
                $table->string('category')->nullable()->index(); // Nhóm vật tư
                $table->string('unit')->default('Tấm'); // Đơn vị tính
                $table->double('cost_price')->default(0); // Giá vốn / Giá nhập
                $table->double('selling_price')->default(0); // Giá bán nếu có
                $table->double('min_stock')->default(50); // Định mức tồn tối thiểu
                $table->double('max_stock')->nullable(); // Định mức tồn tối đa
                $table->double('current_stock')->default(0); // Tồn kho hiện tại
                $table->double('initial_stock')->default(0); // Tồn đầu kỳ
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->string('status')->default('active'); // active / inactive
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 3. Inventory Receipts (Phiếu Nhập kho - PNK)
        if (!Schema::hasTable('inventory_receipts')) {
            Schema::create('inventory_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->index(); // PNK...
                $table->date('date')->index();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->string('supplier_name')->nullable();
                $table->string('deliverer')->nullable();
                $table->string('receiver')->nullable();
                $table->double('total_quantity')->default(0);
                $table->double('total_amount')->default(0);
                $table->text('notes')->nullable();
                $table->string('status')->default('completed'); // draft, completed, cancelled
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Inventory Receipt Items
        if (!Schema::hasTable('inventory_receipt_items')) {
            Schema::create('inventory_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('receipt_id')->constrained('inventory_receipts')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
                $table->double('quantity');
                $table->double('unit_price')->default(0);
                $table->double('total_price')->default(0);
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. Inventory Issues (Phiếu Xuất kho - PXK)
        if (!Schema::hasTable('inventory_issues')) {
            Schema::create('inventory_issues', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->index(); // PXK...
                $table->date('date')->index();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->string('recipient')->nullable(); // Người / bộ phận nhận
                $table->string('deliverer')->nullable(); // Người xuất
                $table->string('reason')->nullable(); // Lý do xuất (Sản xuất, Bán hàng, Xuất hủy...)
                $table->double('total_quantity')->default(0);
                $table->double('total_amount')->default(0);
                $table->text('notes')->nullable();
                $table->string('status')->default('completed'); // draft, completed, cancelled
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 6. Inventory Issue Items
        if (!Schema::hasTable('inventory_issue_items')) {
            Schema::create('inventory_issue_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('issue_id')->constrained('inventory_issues')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
                $table->double('quantity');
                $table->double('unit_price')->default(0);
                $table->double('total_price')->default(0);
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }

        // 7. Inventory Stocktakes (Phiếu Kiểm kê kho - PKK)
        if (!Schema::hasTable('inventory_stocktakes')) {
            Schema::create('inventory_stocktakes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->index(); // PKK...
                $table->date('date')->index();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->string('creator_name')->nullable();
                $table->double('total_book_quantity')->default(0);
                $table->double('total_actual_quantity')->default(0);
                $table->double('total_difference')->default(0);
                $table->text('notes')->nullable();
                $table->string('status')->default('draft'); // draft (đang kiểm kê), balanced (đã cân bằng kho)
                $table->timestamp('balanced_at')->nullable();
                $table->foreignId('balanced_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 8. Inventory Stocktake Items
        if (!Schema::hasTable('inventory_stocktake_items')) {
            Schema::create('inventory_stocktake_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stocktake_id')->constrained('inventory_stocktakes')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
                $table->double('book_quantity')->default(0); // Tồn sổ sách
                $table->double('actual_quantity')->default(0); // Tồn thực tế
                $table->double('difference')->default(0); // actual - book
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }

        // 9. Inventory Transactions (Sổ kho / Thẻ kho)
        if (!Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
                $table->date('date')->index();
                $table->string('type')->index(); // 'receipt', 'issue', 'stocktake', 'initial'
                $table->string('voucher_code')->index(); // PNK..., PXK..., PKK...
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_type')->nullable();
                $table->double('in_qty')->default(0);
                $table->double('out_qty')->default(0);
                $table->double('stock_after')->default(0);
                $table->double('unit_price')->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_stocktake_items');
        Schema::dropIfExists('inventory_stocktakes');
        Schema::dropIfExists('inventory_issue_items');
        Schema::dropIfExists('inventory_issues');
        Schema::dropIfExists('inventory_receipt_items');
        Schema::dropIfExists('inventory_receipts');
        Schema::dropIfExists('materials');
    }
};
