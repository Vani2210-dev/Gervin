<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dc_stocks', function (Blueprint $table) {
            $table->id();

            // Mã hàng: liên kết với wood_boards (color_code như PVC20, PVC28...)
            $table->string('color_code', 50)->comment('Mã màu/mã hàng ván (PVC20, PVC28...)');

            // Ghi chú phụ (ví dụ: "2mat", "1mat"...)
            $table->string('note', 100)->nullable()->comment('Ghi chú phụ về tấm');

            // Kích thước tấm dư
            $table->unsignedInteger('height')->comment('Chiều cao (mm)');
            $table->unsignedInteger('width')->comment('Chiều rộng (mm)');

            // Số lượng tấm
            $table->unsignedSmallInteger('quantity')->default(1);

            // Vị trí trong kho
            $table->string('location', 50)->nullable()->comment('Vị trí kho (D14, SO4...)');

            // Trạng thái: available, used, reserved
            $table->enum('status', ['available', 'used', 'reserved'])->default('available');

            // Người thêm vào kho
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('color_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dc_stocks');
    }
};
