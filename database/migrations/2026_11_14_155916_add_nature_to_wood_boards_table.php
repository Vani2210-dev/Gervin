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
        Schema::table('wood_boards', function (Blueprint $table) {
            if (!Schema::hasColumn('wood_boards', 'nature')) {
                $table->string('nature', 100)->nullable()->default('Vật tư hàng hóa')->after('foil_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wood_boards', function (Blueprint $table) {
            if (Schema::hasColumn('wood_boards', 'nature')) {
                $table->dropColumn('nature');
            }
        });
    }
};
