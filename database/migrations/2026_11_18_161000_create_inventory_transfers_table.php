<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_transfers')) {
            Schema::create('inventory_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->index(); // PCK...
                $table->date('date')->index();
                $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
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

        if (!Schema::hasTable('inventory_transfer_items')) {
            Schema::create('inventory_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transfer_id')->constrained('inventory_transfers')->cascadeOnDelete();
                $table->foreignId('from_material_id')->nullable()->constrained('materials')->nullOnDelete();
                $table->foreignId('to_material_id')->nullable()->constrained('materials')->nullOnDelete();
                $table->foreignId('wood_board_id')->nullable()->constrained('wood_boards')->nullOnDelete();
                $table->foreignId('wood_board_price_id')->nullable()->constrained('wood_board_prices')->nullOnDelete();
                $table->string('item_type')->nullable();
                $table->string('code')->nullable();
                $table->string('name')->nullable();
                $table->string('unit')->nullable();
                $table->double('height')->nullable();
                $table->double('width')->nullable();
                $table->double('quantity');
                $table->double('unit_price')->default(0);
                $table->double('total_price')->default(0);
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfer_items');
        Schema::dropIfExists('inventory_transfers');
    }
};
