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
        Schema::create('manufacture_stamp_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacture_order_id')->constrained('manufacture_orders')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('users')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_item_codes', function (Blueprint $table) {
            $table->foreignId('assigned_worker_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('glass_order_item_codes', function (Blueprint $table) {
            $table->foreignId('assigned_worker_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('min_late_order_item_codes', function (Blueprint $table) {
            $table->foreignId('assigned_worker_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::dropIfExists('manufacture_stamp_distributions');
    }
};
