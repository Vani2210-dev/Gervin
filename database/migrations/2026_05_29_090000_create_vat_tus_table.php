<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_tus', function (Blueprint $table) {
            $table->id();
            $table->string('ten_vat_tu');
            $table->string('phan_loai')->nullable();
            $table->string('don_vi_tinh')->nullable();
            $table->decimal('so_luong_ton', 15, 2)->default(0);
            $table->decimal('ton_toi_thieu', 15, 2)->default(0);
            $table->decimal('don_gia', 15, 0)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_tus');
    }
};
