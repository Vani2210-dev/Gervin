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
            $table->string('old_size')->nullable()->after('notes');
        });
        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->string('old_size')->nullable()->after('notes');
        });
        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->string('old_size')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->dropColumn('old_size');
        });
        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->dropColumn('old_size');
        });
        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->dropColumn('old_size');
        });
    }
};
