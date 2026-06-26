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
        Schema::create('glass_prices', function (Blueprint $table) {
            $table->id();
            $table->string('category_name')->nullable();
            $table->string('stt')->nullable();
            $table->text('product_name')->nullable();
            $table->string('aluminum_color')->nullable();
            $table->string('glass_color')->nullable();
            $table->string('unit')->nullable();
            $table->double('price')->default(0);
            $table->string('code')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glass_prices');
    }
};
