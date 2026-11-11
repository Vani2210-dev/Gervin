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
        Schema::table('minlate_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('minlate_prices', 'default_formula')) {
                $table->string('default_formula')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('minlate_prices', function (Blueprint $table) {
            if (Schema::hasColumn('minlate_prices', 'default_formula')) {
                $table->dropColumn('default_formula');
            }
        });
    }
};

