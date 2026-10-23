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
        Schema::table('order_supplies', function (Blueprint $table) {
            if (!Schema::hasColumn('order_supplies', 'inventory_action')) {
                $table->string('inventory_action', 20)->default('add')->after('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_supplies', function (Blueprint $table) {
            if (Schema::hasColumn('order_supplies', 'inventory_action')) {
                $table->dropColumn('inventory_action');
            }
        });
    }
};
