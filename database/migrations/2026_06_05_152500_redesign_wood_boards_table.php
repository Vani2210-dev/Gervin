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
        // Drop old table if exists
        Schema::dropIfExists('wood_boards');

        Schema::create('wood_boards', function (Blueprint $table) {
            $table->id();
            $table->string('price_group')->nullable();
            $table->string('color_code')->unique();

            // Set 1: MDF 1 mặt Acrylic
            $table->string('name_1')->nullable();
            $table->decimal('price_board_1', 15, 2)->default(0);
            $table->decimal('price_m2_1', 15, 2)->default(0);

            // Set 2: MDF 2 mặt Acrylic
            $table->string('code_2')->nullable();
            $table->string('name_2')->nullable();
            $table->decimal('price_board_2', 15, 2)->default(0);
            $table->decimal('price_m2_2', 15, 2)->default(0);

            // Set 3: Cốt nhựa 1 mặt Acrylic
            $table->string('code_3')->nullable();
            $table->string('name_3')->nullable();
            $table->decimal('price_board_3', 15, 2)->default(0);
            $table->decimal('price_m2_3', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wood_boards');
    }
};
