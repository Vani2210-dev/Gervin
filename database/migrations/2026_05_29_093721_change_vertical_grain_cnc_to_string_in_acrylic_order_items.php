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
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->string('vertical_grain_cnc')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->boolean('vertical_grain_cnc')->default(false)->change();
        });
    }
};
