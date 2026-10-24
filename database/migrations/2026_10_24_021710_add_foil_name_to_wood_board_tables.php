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
        if (Schema::hasTable('wood_board_prices') && !Schema::hasColumn('wood_board_prices', 'foil_name')) {
            Schema::table('wood_board_prices', function (Blueprint $table) {
                $table->string('foil_name', 255)->nullable()->after('name');
            });
        }

        if (Schema::hasTable('wood_boards') && !Schema::hasColumn('wood_boards', 'foil_name')) {
            Schema::table('wood_boards', function (Blueprint $table) {
                $table->string('foil_name', 255)->nullable()->after('color_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('wood_board_prices') && Schema::hasColumn('wood_board_prices', 'foil_name')) {
            Schema::table('wood_board_prices', function (Blueprint $table) {
                $table->dropColumn('foil_name');
            });
        }

        if (Schema::hasTable('wood_boards') && Schema::hasColumn('wood_boards', 'foil_name')) {
            Schema::table('wood_boards', function (Blueprint $table) {
                $table->dropColumn('foil_name');
            });
        }
    }
};