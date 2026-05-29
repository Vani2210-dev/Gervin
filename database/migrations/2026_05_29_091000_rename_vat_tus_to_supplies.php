<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('vat_tus', 'supplies');

        Schema::table('supplies', function (Blueprint $table) {
            $table->renameColumn('ten_vat_tu',    'name');
            $table->renameColumn('phan_loai',     'category');
            $table->renameColumn('don_vi_tinh',   'unit');
            $table->renameColumn('so_luong_ton',  'stock_quantity');
            $table->renameColumn('ton_toi_thieu', 'min_stock');
            $table->renameColumn('don_gia',       'unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->renameColumn('name',           'ten_vat_tu');
            $table->renameColumn('category',       'phan_loai');
            $table->renameColumn('unit',           'don_vi_tinh');
            $table->renameColumn('stock_quantity', 'so_luong_ton');
            $table->renameColumn('min_stock',      'ton_toi_thieu');
            $table->renameColumn('unit_price',     'don_gia');
        });

        Schema::rename('supplies', 'vat_tus');
    }
};
