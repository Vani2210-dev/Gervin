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
        if (!Schema::hasTable('qr_measurements')) {
            Schema::create('qr_measurements', function (Blueprint $table) {
                $table->id();
                $table->string('qr_code', 191)->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('order_code', 100)->nullable()->index();
                $table->string('customer_name', 255)->nullable();
                $table->string('item_type', 50)->nullable(); // acrylic, glass, min_late
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('product_name', 255)->nullable();

                // Yêu cầu (thiết kế)
                $table->double('required_height')->nullable(); // Dài yêu cầu (mm)
                $table->double('required_width')->nullable();  // Rộng yêu cầu (mm)

                // Thực tế đo
                $table->double('actual_height'); // Dài thực tế đo (mm)
                $table->double('actual_width');  // Rộng thực tế đo (mm)

                // Sai lệch tính toán
                $table->double('diff_height')->nullable(); // actual_height - required_height
                $table->double('diff_width')->nullable();  // actual_width - required_width
                $table->double('tolerance')->default(1.0); // Sai số cho phép chung (mm)
                $table->double('tolerance_height')->nullable();
                $table->double('tolerance_width')->nullable();

                // Đánh giá
                $table->boolean('is_passed')->default(true);
                $table->string('status', 50)->default('passed'); // passed, failed, warning

                // Thiết bị và người đo
                $table->string('device_id', 100)->nullable();
                $table->string('operator_name', 150)->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('measured_at')->nullable();
                $table->json('raw_payload')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_measurements');
    }
};

