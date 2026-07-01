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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('customer_policy');
            $table->double('discount_amount')->default(0)->after('discount_percent');
            $table->decimal('vat_percent', 5, 2)->default(0)->after('discount_amount');
            $table->double('vat_amount')->default(0)->after('vat_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'discount_percent',
                'discount_amount',
                'vat_percent',
                'vat_amount',
            ]);
        });
    }
};
