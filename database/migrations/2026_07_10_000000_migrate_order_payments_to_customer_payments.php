<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename order_payments to customer_payments
        Schema::rename('order_payments', 'customer_payments');

        Schema::table('customer_payments', function (Blueprint $table) {
            // 2. Add customer_id (nullable initially so we can fill it for existing records)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            
            // 3. Make order_id nullable
            $table->unsignedBigInteger('order_id')->nullable()->change();
        });

        // 4. Migrate existing payment records to map customer_id
        DB::statement("
            UPDATE customer_payments 
            JOIN orders ON customer_payments.order_id = orders.id 
            SET customer_payments.customer_id = orders.customer_id
        ");

        // 5. Alter customer_id to be NOT NULL now that data is migrated
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            // Reverse order_id back to not nullable
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
            
            // Drop customer_id
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });

        Schema::rename('customer_payments', 'order_payments');
    }
};
