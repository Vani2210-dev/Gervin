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
        Schema::create('cnc_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            
            // Offset parameters
            $table->integer('offset_left')->nullable();
            $table->integer('offset_right')->nullable();
            $table->integer('offset_top')->nullable();
            $table->integer('offset_bottom')->nullable();
            
            // Milling 1 parameters
            $table->integer('mill_left')->nullable();
            $table->integer('mill_right')->nullable();
            $table->integer('mill_top')->nullable();
            $table->integer('mill_bottom')->nullable();
            $table->integer('mill_width')->nullable();
            $table->integer('mill_depth')->nullable();
            
            // Milling 2 parameters
            $table->integer('mill_left_2')->nullable();
            $table->integer('mill_right_2')->nullable();
            $table->integer('mill_top_2')->nullable();
            $table->integer('mill_bottom_2')->nullable();
            $table->integer('mill_width_2')->nullable();
            $table->integer('mill_depth_2')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnc_templates');
    }
};
