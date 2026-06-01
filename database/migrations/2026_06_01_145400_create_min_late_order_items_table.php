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
        Schema::create('min_late_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_supply_id')->constrained('order_supplies')->onDelete('cascade');
            $table->string('name');
            $table->json('size')->nullable();
            $table->integer('quantity')->default(1);
            $table->json('edge_gluing')->nullable();
            $table->decimal('straight_paste_length', 10, 2)->default(0);
            $table->decimal('beveled_length', 10, 2)->default(0);
            $table->decimal('vat_moi_length', 10, 2)->default(0);
            $table->decimal('ban_rong_40_59', 10, 2)->default(0);
            $table->decimal('ban_rong_17_39', 10, 2)->default(0);
            $table->decimal('ban_rong_25_35', 10, 2)->default(0);
            $table->decimal('beveled_handle', 10, 2)->default(0);
            $table->integer('cnc')->default(0);
            $table->string('direction')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('min_late_order_items');
    }
};
