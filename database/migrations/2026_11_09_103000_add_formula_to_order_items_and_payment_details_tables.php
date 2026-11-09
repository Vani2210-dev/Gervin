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
            $table->text('formula')->nullable()->after('notes');
        });
        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->text('formula')->nullable()->after('notes');
        });
        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->text('formula')->nullable()->after('notes');
        });
        Schema::table('payment_details', function (Blueprint $table) {
            $table->text('formula')->nullable()->after('total');
        });
        Schema::table('order_supplies', function (Blueprint $table) {
            $table->text('formula')->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            $table->dropColumn('formula');
        });
        Schema::table('glass_order_items', function (Blueprint $table) {
            $table->dropColumn('formula');
        });
        Schema::table('min_late_order_items', function (Blueprint $table) {
            $table->dropColumn('formula');
        });
        Schema::table('payment_details', function (Blueprint $table) {
            $table->dropColumn('formula');
        });
        Schema::table('order_supplies', function (Blueprint $table) {
            $table->dropColumn('formula');
        });
    }
};

