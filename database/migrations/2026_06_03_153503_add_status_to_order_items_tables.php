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
            if (!Schema::hasColumn('acrylic_order_items', 'status')) {
                $table->json('status')->nullable()->after('vertical_grain_cnc');
            }
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('glass_order_items', 'status')) {
                $table->json('status')->nullable()->after('notes');
            }
        });

        Schema::table('min_late_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('min_late_order_items', 'notes')) {
                $table->text('notes')->nullable()->after('direction');
            }
            if (!Schema::hasColumn('min_late_order_items', 'status')) {
                $table->json('status')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('acrylic_order_items', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('glass_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('glass_order_items', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('min_late_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('min_late_order_items', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('min_late_order_items', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
