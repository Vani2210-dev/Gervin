<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packing_packages', function (Blueprint $table) {
            // Thêm các cột cho chức năng Giao hàng (Delivery) sau cột ghi chú xuất xưởng.
            $table->timestamp('delivered_at')->nullable()->after('dispatched_note');
            $table->text('delivered_note')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('packing_packages', function (Blueprint $table) {
            // Xóa các cột đã thêm khi rollback migration.
            $table->dropColumn(['delivered_at', 'delivered_note']);
        });
    }
};
