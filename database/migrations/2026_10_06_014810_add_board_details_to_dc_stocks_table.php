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
        Schema::table('dc_stocks', function (Blueprint $table) {
            $table->string('board_type', 100)->nullable()->after('color_code')->comment('Loại ván (MDF 1 mặt, MDF 2 mặt, Cốt nhựa...)');
            $table->string('board_code', 100)->nullable()->after('board_type')->comment('Mã ván hoàn chỉnh (prefix + color_code)');
            $table->string('thickness', 50)->nullable()->after('board_code')->comment('Độ dày (17mm, 9mm...)');
            $table->foreignId('wood_board_type_id')->nullable()->after('thickness')->constrained('wood_board_types')->nullOnDelete();
            $table->foreignId('wood_board_id')->nullable()->after('wood_board_type_id')->constrained('wood_boards')->nullOnDelete();
            $table->index('board_type');
            $table->index('board_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dc_stocks', function (Blueprint $table) {
            $table->dropForeign(['wood_board_type_id']);
            $table->dropForeign(['wood_board_id']);
            $table->dropIndex(['board_type']);
            $table->dropIndex(['board_code']);
            $table->dropColumn(['board_type', 'board_code', 'thickness', 'wood_board_type_id', 'wood_board_id']);
        });
    }
};
