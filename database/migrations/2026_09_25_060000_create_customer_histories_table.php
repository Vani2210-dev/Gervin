<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('market_group_id')->nullable()->constrained('market_groups')->nullOnDelete();
            $table->string('action', 50)->default('updated');
            $table->text('summary')->nullable();
            $table->json('changes')->nullable();
            $table->json('photos')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index(['market_group_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_histories');
    }
};
