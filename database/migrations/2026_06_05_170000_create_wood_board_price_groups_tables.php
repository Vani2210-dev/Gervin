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
        Schema::create('wood_board_price_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('wood_board_price_group_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wood_board_price_group_id')
                  ->constrained('wood_board_price_groups')
                  ->onDelete('cascade');
            $table->foreignId('wood_board_type_id')
                  ->constrained('wood_board_types')
                  ->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('thickness')->nullable();
            $table->decimal('price_board', 15, 2)->default(0);
            $table->decimal('price_m2', 15, 2)->default(0);
            $table->timestamps();

            // Ensure unique price entry per type in each group
            $table->unique(['wood_board_price_group_id', 'wood_board_type_id'], 'wbp_group_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wood_board_price_group_prices');
        Schema::dropIfExists('wood_board_price_groups');
    }
};
