<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('materials') && !Schema::hasColumn('materials', 'wood_board_id')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->foreignId('wood_board_id')->nullable()->after('warehouse_id')->constrained('wood_boards')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('materials') && Schema::hasColumn('materials', 'wood_board_id')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropForeign(['wood_board_id']);
                $table->dropColumn('wood_board_id');
            });
        }
    }
};
