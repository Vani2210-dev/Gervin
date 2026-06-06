<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('packing_packages', 'shipped_at') && ! Schema::hasColumn('packing_packages', 'dispatched_at')) {
            Schema::table('packing_packages', function (Blueprint $table) {
                // Doi ten cot thoi diem xuat xuong sang thuat ngu dispatch.
                $table->renameColumn('shipped_at', 'dispatched_at');
            });
        }

        if (! Schema::hasColumn('packing_packages', 'dispatched_note')) {
            Schema::table('packing_packages', function (Blueprint $table) {
                // Ghi chu tai thoi diem xac nhan xuat xuong.
                $table->text('dispatched_note')->nullable()->after('dispatched_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('packing_packages', 'dispatched_note')) {
            Schema::table('packing_packages', function (Blueprint $table) {
                $table->dropColumn('dispatched_note');
            });
        }

        if (Schema::hasColumn('packing_packages', 'dispatched_at') && ! Schema::hasColumn('packing_packages', 'shipped_at')) {
            Schema::table('packing_packages', function (Blueprint $table) {
                $table->renameColumn('dispatched_at', 'shipped_at');
            });
        }
    }
};
