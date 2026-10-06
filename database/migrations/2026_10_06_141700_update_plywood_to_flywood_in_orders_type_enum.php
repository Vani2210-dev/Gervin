<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cập nhật dữ liệu cũ nếu có
        DB::table('orders')->where('type', 'plywood')->update(['type' => 'Flywood']);

        // Cập nhật lại ENUM của cột type
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('acrylic', 'min_late', 'glass', 'Flywood', 'service') NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')->where('type', 'Flywood')->update(['type' => 'plywood']);

        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('acrylic', 'min_late', 'glass', 'plywood', 'service') NULL;");
    }
};
