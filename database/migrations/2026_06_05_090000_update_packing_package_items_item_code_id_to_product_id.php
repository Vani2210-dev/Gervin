<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chỉ đổi kiểu cột để lưu trực tiếp product_id thay vì id tự tăng.
        Schema::table('packing_package_items', function (Blueprint $table) {
            $table->string('item_code_id')->change();
        });
    }

    public function down(): void
    {
        // Đổi ngược lại về số nguyên nếu cần rollback.
        Schema::table('packing_package_items', function (Blueprint $table) {
            $table->unsignedBigInteger('item_code_id')->change();
        });
    }
};
