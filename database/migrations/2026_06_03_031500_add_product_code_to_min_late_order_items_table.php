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
        Schema::table('min_late_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('min_late_order_items', 'product_code')) {
                $table->string('product_code', 50)->nullable()->after('order_supply_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('min_late_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('min_late_order_items', 'product_code')) {
                $table->dropColumn('product_code');
            }
        });
    }
};
