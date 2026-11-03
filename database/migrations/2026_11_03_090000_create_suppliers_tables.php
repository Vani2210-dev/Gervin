<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng Nhà cung cấp (Suppliers)
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique()->index(); // NCC00001, NCC00002...
                $table->string('name');
                $table->string('phone', 50)->nullable();
                $table->string('email', 100)->nullable();
                $table->text('address')->nullable();
                $table->string('tax_code', 50)->nullable();
                $table->string('contact_person')->nullable();
                $table->double('initial_debt')->default(0); // Nợ ban đầu
                $table->double('debt')->default(0); // Công nợ hiện tại (phải trả NCC)
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('active'); // active, inactive
                $table->timestamps();
            });
        }

        // 2. Thêm cột supplier_id vào bảng inventory_receipts
        if (Schema::hasTable('inventory_receipts') && !Schema::hasColumn('inventory_receipts', 'supplier_id')) {
            Schema::table('inventory_receipts', function (Blueprint $table) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('warehouse_id')->index();
                $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            });
        }

        // 3. Bảng Phiếu chi / Thanh toán cho Nhà cung cấp (Supplier Payments)
        if (!Schema::hasTable('supplier_payments')) {
            Schema::create('supplier_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('receipt_id')->nullable()->index();
                $table->string('payment_code', 50)->unique()->index(); // PC00001...
                $table->date('payment_date')->index();
                $table->double('amount')->default(0);
                $table->string('payment_method', 50)->default('cash'); // cash, bank_transfer, etc.
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
                $table->foreign('receipt_id')->references('id')->on('inventory_receipts')->nullOnDelete();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');

        if (Schema::hasTable('inventory_receipts') && Schema::hasColumn('inventory_receipts', 'supplier_id')) {
            Schema::table('inventory_receipts', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            });
        }

        Schema::dropIfExists('suppliers');
    }
};

