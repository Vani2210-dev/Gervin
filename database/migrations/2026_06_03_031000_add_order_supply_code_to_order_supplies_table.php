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
            if (!Schema::hasColumn('order_supplies', 'order_supply_code')) {
                $table->string('order_supply_code')->nullable()->after('order_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_supplies', function (Blueprint $table) {
            if (Schema::hasColumn('order_supplies', 'order_supply_code')) {
                $table->dropColumn('order_supply_code');
            }
        });
    }
};
