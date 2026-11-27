<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('customers', 'is_retail')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->boolean('is_retail')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'is_retail')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('is_retail');
            });
        }
    }
};
