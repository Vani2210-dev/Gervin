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
        $prices = [
            [
                'code' => 'LIC1',
                'category_name' => 'Dịch vụ Min-late',
                'product_name' => 'Gia công cắt dán sửa tấm',
                'unit' => 'Tấm',
                'price' => 0,
                'stt' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ML48',
                'category_name' => 'Dịch vụ Min-late',
                'product_name' => 'Dán chỉ thẳng',
                'unit' => 'm',
                'price' => 0,
                'stt' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ML49',
                'category_name' => 'Dịch vụ Min-late',
                'product_name' => 'Dán chỉ vát',
                'unit' => 'm',
                'price' => 0,
                'stt' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($prices as $p) {
            $exists = DB::table('minlate_prices')->where('code', $p['code'])->first();
            if (!$exists) {
                DB::table('minlate_prices')->insert($p);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('minlate_prices')->whereIn('code', ['LIC1', 'ML48', 'ML49'])->delete();
    }
};
