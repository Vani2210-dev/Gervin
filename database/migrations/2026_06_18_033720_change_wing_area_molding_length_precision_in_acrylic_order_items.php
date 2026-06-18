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
            $table->decimal('wing_area', 14, 6)->nullable()->change();
            $table->decimal('molding_length', 14, 6)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->decimal('wing_area', 10, 2)->nullable()->change();
            $table->decimal('molding_length', 10, 2)->nullable()->change();
        });
    }
};
