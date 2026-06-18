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
            $table->double('height')->nullable()->change();
            $table->double('width')->nullable()->change();
            $table->double('unit_price')->default(0)->change();
            $table->double('total_price')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->decimal('height', 10, 2)->nullable()->change();
            $table->decimal('width', 10, 2)->nullable()->change();
            $table->decimal('unit_price', 15, 2)->default(0)->change();
            $table->decimal('total_price', 15, 2)->default(0)->change();
        });
    }
};
