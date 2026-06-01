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
        Schema::create('acrylic_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acrylic_order_id')->constrained('acrylic_orders')->onDelete('cascade');
            $table->string('product_code')->nullable();
            $table->string('product_name');
            $table->decimal('height', 10, 2)->nullable(); // Cao
            $table->decimal('width', 10, 2)->nullable(); // Rộng
            $table->tinyInteger('grain_direction')->default(0); // Vân: 0 hoặc 2
            $table->string('edge_bevel')->nullable(); // Cạnh Vát
            $table->decimal('wing_area', 10, 2)->nullable(); // Cánh (m2)
            $table->decimal('molding_length', 10, 2)->nullable(); // Thanh Phào (m)
            $table->integer('quantity')->default(1); // Số lượng
            $table->decimal('unit_price', 15, 2)->default(0); // Đơn giá
            $table->decimal('total_price', 15, 2)->default(0); // Thành tiền
            $table->text('notes')->nullable(); // Ghi chú
            $table->string('bevel')->nullable(); // Vát
            $table->boolean('vertical_grain_cnc')->default(false); // Vân dọc CNC
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acrylic_order_items');
    }
};
