<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('wood_board_prices');
        Schema::dropIfExists('wood_board_types');
        Schema::dropIfExists('wood_boards');

        Schema::create('wood_board_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('prefix')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('wood_boards', function (Blueprint $table) {
            $table->id();
            $table->string('price_group')->nullable();
            $table->string('color_code')->unique();
            $table->timestamps();
        });

        Schema::create('wood_board_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wood_board_id')->constrained('wood_boards')->onDelete('cascade');
            $table->foreignId('wood_board_type_id')->constrained('wood_board_types')->onDelete('cascade');
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('thickness')->nullable();
            $table->decimal('price_board', 15, 2)->default(0);
            $table->decimal('price_m2', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['wood_board_id', 'wood_board_type_id']);
        });

        // Insert initial default types
        DB::table('wood_board_types')->insert([
            [
                'name' => 'MDF 1 MẶT ACRYLIC (Mã = Mã màu)',
                'prefix' => '',
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MDF 2 MẶT ACRYLIC',
                'prefix' => '.TP.2M',
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CỐT NHỰA 1 MẶT ACRYLIC',
                'prefix' => '.TP.PVC',
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wood_board_prices');
        Schema::dropIfExists('wood_board_types');
        Schema::dropIfExists('wood_boards');
    }
};
