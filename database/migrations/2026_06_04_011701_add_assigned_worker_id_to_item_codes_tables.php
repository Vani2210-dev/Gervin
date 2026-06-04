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
        Schema::table('acrylic_order_item_codes', function (Blueprint $table) {
            $table->foreignId('assigned_worker_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });

        Schema::table('glass_order_item_codes', function (Blueprint $table) {
            $table->foreignId('assigned_worker_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });

        Schema::table('min_late_order_item_codes', function (Blueprint $table) {
            $table->foreignId('assigned_worker_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('min_late_order_item_codes', function (Blueprint $table) {
            $table->dropForeign(['assigned_worker_id']);
            $table->dropColumn('assigned_worker_id');
        });

        Schema::table('glass_order_item_codes', function (Blueprint $table) {
            $table->dropForeign(['assigned_worker_id']);
            $table->dropColumn('assigned_worker_id');
        });

        Schema::table('acrylic_order_item_codes', function (Blueprint $table) {
            $table->dropForeign(['assigned_worker_id']);
            $table->dropColumn('assigned_worker_id');
        });
    }
};
