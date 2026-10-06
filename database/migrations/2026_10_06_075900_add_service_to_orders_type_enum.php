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
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('acrylic', 'min_late', 'glass', 'Flywppd', 'service') NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('acrylic', 'min_late', 'glass', 'Flywppd') NULL;");
    }
};
