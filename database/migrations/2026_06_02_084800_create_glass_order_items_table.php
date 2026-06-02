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
        Schema::create('glass_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_supply_id')->constrained('order_supplies')->onDelete('cascade');
            $table->string('product_name'); // Tên sản phẩm
            $table->string('product_code')->nullable(); // Mã SP
            $table->string('wing_opening_direction')->nullable(); // Chiều mở cánh
            $table->string('aluminum_color')->nullable(); // Màu nhôm
            $table->string('glass_color')->nullable(); // Màu kính
            $table->decimal('height', 10, 2)->nullable(); // Kích thước cánh - Dài (mm)
            $table->decimal('width', 10, 2)->nullable(); // Kích thước cánh - Rộng (mm)
            $table->string('unit')->default('Bộ'); // Đơn vị
            $table->integer('wing_quantity')->default(1); // Số lượng cánh
            $table->decimal('area_m2', 10, 2)->nullable(); // Khối lượng (m2)
            $table->decimal('unit_price', 15, 2)->default(0); // Đơn giá
            $table->decimal('total_price', 15, 2)->default(0); // Thành tiền
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glass_order_items');
    }
};
