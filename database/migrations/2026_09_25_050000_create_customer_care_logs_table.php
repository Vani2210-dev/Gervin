<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_care_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('visit_date');
            $table->string('status', 100)->nullable();
            $table->text('feedback')->nullable();
            $table->text('customer_proposal')->nullable();
            $table->text('sale_proposal')->nullable();
            $table->json('photos')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_care_logs');
    }
};
