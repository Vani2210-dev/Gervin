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
        Schema::create('manufacture_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('status')->default('initialized'); // initialized, tech_approved, manager_approved, stamps_received, in_production, completed
            $table->text('notes')->nullable();
            
            $table->foreignId('tech_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tech_approved_at')->nullable();
            
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            
            $table->foreignId('stamps_received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('stamps_received_at')->nullable();
            
            $table->foreignId('production_started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('production_started_at')->nullable();
            
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('manufacture_order_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacture_order_id')->constrained('manufacture_orders')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacture_order_order');
        Schema::dropIfExists('manufacture_orders');
    }
};
