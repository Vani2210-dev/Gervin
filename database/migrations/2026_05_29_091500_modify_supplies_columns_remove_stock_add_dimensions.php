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
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'min_stock']);
            $table->tinyInteger('grain_direction')->default(0)->after('unit')->comment('0: none, 2: grain direction');
            $table->integer('width_mm')->nullable()->after('grain_direction');
            $table->integer('height_mm')->nullable()->after('width_mm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropColumn(['grain_direction', 'width_mm', 'height_mm']);
            $table->decimal('stock_quantity', 10, 2)->default(0)->after('unit');
            $table->decimal('min_stock', 10, 2)->default(0)->after('stock_quantity');
        });
    }
};
