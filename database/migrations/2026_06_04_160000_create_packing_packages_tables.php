<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('draft'); //draft, completed
            $table->foreignId('packed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('packing_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_package_id')->constrained('packing_packages')->cascadeOnDelete();
            $table->string('item_code_type');
            $table->unsignedBigInteger('item_code_id');
            $table->timestamps();

            $table->unique(['item_code_type', 'item_code_id']);
            $table->index(['packing_package_id', 'item_code_type', 'item_code_id'], 'packing_items_package_code_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_package_items');
        Schema::dropIfExists('packing_packages');
    }
};
