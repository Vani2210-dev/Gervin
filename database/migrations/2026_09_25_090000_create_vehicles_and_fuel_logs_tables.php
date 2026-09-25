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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number', 50)->unique()->comment('Biển số xe');
            $table->string('name', 191)->comment('Tên xe / Loại xe');
            $table->string('driver_name', 191)->nullable()->comment('Lái xe / Phụ trách');
            $table->string('driver_phone', 50)->nullable()->comment('Số điện thoại');
            $table->string('fuel_type', 50)->default('diesel')->comment('Loại nhiên liệu: diesel, ron95, ron92...');
            $table->double('initial_km')->default(0)->comment('Số km ban đầu');
            $table->double('current_km')->default(0)->comment('Số km hiện tại');
            $table->double('oil_change_interval_km')->default(5000)->comment('Chu kỳ thay dầu (mặc định 5000km)');
            $table->double('last_oil_change_km')->nullable()->comment('Số km tại lần thay dầu gần nhất');
            $table->date('last_oil_change_date')->nullable()->comment('Ngày thay dầu gần nhất');
            $table->string('status', 30)->default('active')->comment('active: Đang chạy, maintenance: Đang bảo dưỡng, inactive: Ngừng hoạt động');
            $table->text('notes')->nullable()->comment('Ghi chú xe');
            $table->timestamps();
        });

        Schema::create('vehicle_fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->date('date')->comment('Ngày tháng năm');
            $table->string('type', 30)->default('fuel')->comment('fuel: Đổ xăng dầu, oil_change: Thay dầu máy, maintenance: Bảo dưỡng khác');
            $table->double('odometer_km')->nullable()->comment('Số Km công tơ mét');
            $table->double('liters')->nullable()->comment('Số lượng (lít)');
            $table->double('unit_price')->nullable()->comment('Đơn giá');
            $table->double('total_price')->nullable()->comment('Số tiền = Đơn giá * Số lít');
            $table->double('trip_km')->nullable()->comment('Tổng số km đi được với lần đổ này');
            $table->double('km_per_liter')->nullable()->comment('1 lít đi được số cây = trip_km / liters');
            $table->double('km_since_oil_change')->nullable()->comment('Số km lũy kế kể từ lần thay dầu máy gần nhất');
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_fuel_logs');
        Schema::dropIfExists('vehicles');
    }
};
