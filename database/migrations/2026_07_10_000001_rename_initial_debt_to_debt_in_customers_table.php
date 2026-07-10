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
        // 1. Rename column initial_debt to debt
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('initial_debt', 'debt');
        });

        // 2. Recalculate debt column for all existing customers to match their current actual debt
        $customers = DB::table('customers')->get();
        foreach ($customers as $customer) {
            // Sum of all valid orders (not draft, pending, or cancelled)
            $ordersSum = DB::table('orders')
                ->where('customer_id', $customer->id)
                ->whereNotIn('status', ['draft', 'pending', 'cancelled'])
                ->sum('total_amount');

            // Sum of all payments
            $paymentsSum = DB::table('customer_payments')
                ->where('customer_id', $customer->id)
                ->sum('amount');

            // New debt = old initial_debt (now debt) + ordersSum - paymentsSum
            $newDebt = ($customer->debt ?? 0) + $ordersSum - $paymentsSum;

            DB::table('customers')
                ->where('id', $customer->id)
                ->update(['debt' => $newDebt]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('debt', 'initial_debt');
        });
    }
};
