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
        Schema::create('pressing_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('purpose')->default('stock'); // 'stock' | 'order'
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('wood_board_id')->nullable()->constrained('wood_boards')->nullOnDelete();
            $table->string('material_name');
            $table->string('core_material')->nullable();
            $table->string('thickness')->nullable();
            $table->string('dimensions')->default('1220 x 2440');
            $table->integer('qty_needed')->default(1);
            $table->integer('qty_done')->default(0);
            $table->string('status')->default('processing'); // 'processing', 'completed', 'cancelled'
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pressing_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pressing_order_id')->constrained('pressing_orders')->cascadeOnDelete();
            $table->string('piece_code')->unique()->index();
            $table->integer('item_index')->default(1);
            $table->string('status')->default('pending'); // 'pending', 'pressed', 'used'
            $table->json('status_logs')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pressing_order_items');
        Schema::dropIfExists('pressing_orders');
    }
};
