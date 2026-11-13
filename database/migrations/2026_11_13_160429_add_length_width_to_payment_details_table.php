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
        Schema::table('payment_details', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_details', 'length')) {
                $table->double('length')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('payment_details', 'width')) {
                $table->double('width')->nullable()->after('length');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_details', function (Blueprint $table) {
            if (Schema::hasColumn('payment_details', 'width')) {
                $table->dropColumn('width');
            }
            if (Schema::hasColumn('payment_details', 'length')) {
                $table->dropColumn('length');
            }
        });
    }
};
