<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_code')->nullable()->unique()->after('id');
        });

        // Tự động gán mã nhân viên mặc định cho các user hiện có
        $users = DB::table('users')->orderBy('id')->get();
        foreach ($users as $index => $u) {
            $code = 'NV' . str_pad($u->id, 4, '0', STR_PAD_LEFT);
            DB::table('users')->where('id', $u->id)->update(['user_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_code');
        });
    }
};
