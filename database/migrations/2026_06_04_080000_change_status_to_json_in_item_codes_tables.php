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
        // 1. Clean up existing string values to valid JSON arrays
        foreach (['acrylic_order_item_codes', 'glass_order_item_codes', 'min_late_order_item_codes'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->get()->each(function ($row) use ($table) {
                    $status = $row->status;
                    // Check if it is already valid json
                    json_decode($status);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $newStatus = json_encode([$status ?: 'pending']);
                        DB::table($table)->where('id', $row->id)->update(['status' => $newStatus]);
                    }
                });
            }
        }

        // 2. Change column types to JSON
        Schema::table('acrylic_order_item_codes', function (Blueprint $table) {
            $table->json('status')->change();
        });

        Schema::table('glass_order_item_codes', function (Blueprint $table) {
            $table->json('status')->change();
        });

        Schema::table('min_late_order_item_codes', function (Blueprint $table) {
            $table->json('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrylic_order_item_codes', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        Schema::table('glass_order_item_codes', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        Schema::table('min_late_order_item_codes', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
