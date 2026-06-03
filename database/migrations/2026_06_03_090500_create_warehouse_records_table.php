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
        Schema::create('warehouse_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('voucher_no')->nullable();
            $table->date('date')->nullable();
            $table->text('content')->nullable();
            $table->string('exporter')->nullable();
            $table->string('receiver')->nullable();
            $table->json('in_data')->nullable(); // quantities per size for Nhập
            $table->json('out_data')->nullable(); // quantities per size for Xuất
            $table->json('stock_data')->nullable(); // current stock per size
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_records');
    }
};
