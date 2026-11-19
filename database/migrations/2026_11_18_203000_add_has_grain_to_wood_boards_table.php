<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wood_boards') && !Schema::hasColumn('wood_boards', 'has_grain')) {
            Schema::table('wood_boards', function (Blueprint $table) {
                $table->boolean('has_grain')->default(false)->after('color_code')->index();
            });

            // Tự động kích hoạt has_grain = true cho các mã màu nhũ hoặc chống xước hiện có (có dấu * hoặc chữ nhu / xuoc)
            DB::table('wood_boards')
                ->where('color_code', 'like', '%*%')
                ->orWhere('color_code', 'like', '%nhu%')
                ->orWhere('color_code', 'like', '%xuoc%')
                ->orWhere('notes', 'like', '%nhu%')
                ->orWhere('notes', 'like', '%xuoc%')
                ->orWhere('foil_name', 'like', '%nhu%')
                ->orWhere('foil_name', 'like', '%xuoc%')
                ->update(['has_grain' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wood_boards') && Schema::hasColumn('wood_boards', 'has_grain')) {
            Schema::table('wood_boards', function (Blueprint $table) {
                $table->dropColumn('has_grain');
            });
        }
    }
};
