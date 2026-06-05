<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packing_packages', function (Blueprint $table) {
            // Cột này đánh dấu thời điểm kiện được xác nhận xuất xưởng.
            $table->timestamp('dispatched_at')->nullable()->after('packed_by');
        });
    }

    public function down(): void
    {
        Schema::table('packing_packages', function (Blueprint $table) {
            $table->dropColumn('dispatched_at');
        });
    }
};
