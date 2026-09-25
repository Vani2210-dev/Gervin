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
        if (!Schema::hasTable('market_groups')) {
            Schema::create('market_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });

            // Seed default 5 market groups: A, B, C, D, E
            $now = now();
            DB::table('market_groups')->insert([
                ['name' => 'Nhóm A', 'code' => 'A', 'description' => 'Nhóm thị trường A', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Nhóm B', 'code' => 'B', 'description' => 'Nhóm thị trường B', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Nhóm C', 'code' => 'C', 'description' => 'Nhóm thị trường C', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Nhóm D', 'code' => 'D', 'description' => 'Nhóm thị trường D', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Nhóm E', 'code' => 'E', 'description' => 'Nhóm thị trường E', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (!Schema::hasTable('market_group_user')) {
            Schema::create('market_group_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('market_group_id')->constrained('market_groups')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['market_group_id', 'user_id']);
            });
        }

        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'market_group_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('market_group_id')->nullable()->after('policy')->constrained('market_groups')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'market_group_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['market_group_id']);
                $table->dropColumn('market_group_id');
            });
        }

        Schema::dropIfExists('market_group_user');
        Schema::dropIfExists('market_groups');
    }
};
