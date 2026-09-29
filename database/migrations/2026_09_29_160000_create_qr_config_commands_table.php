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
        Schema::create('qr_config_commands', function (Blueprint $table) {
            $table->id();
            $table->integer('step')->default(1);
            $table->string('badge')->nullable();
            $table->string('title');
            $table->text('desc')->nullable();
            $table->text('cmd');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed 5 default commands
        $defaults = [
            [
                'step'       => 1,
                'badge'      => 'Bước 1: Reset',
                'title'      => 'Khôi phục cài đặt gốc',
                'desc'       => 'Đặt lại toàn bộ thông số máy quét về mặc định ban đầu',
                'cmd'        => '<cmd>rk_reset',
                'sort_order' => 1,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'step'       => 2,
                'badge'      => 'Bước 2: Wi-Fi',
                'title'      => 'Kết nối Wi-Fi xưởng',
                'desc'       => 'SSID: "CTY GERVIN - XUONG" | Pass: "68686868"',
                'cmd'        => '<cmd>wifi -ssid "CTY GERVIN - XUONG" -pass "68686868"',
                'sort_order' => 2,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'step'       => 3,
                'badge'      => 'Bước 3: Server',
                'title'      => 'Cấu hình Máy chủ Server',
                'desc'       => 'URL: https://gervinwood.vn/scan -dup 1 -queue 20',
                'cmd'        => '<cmd>server -url "https://gervinwood.vn/scan" -dup 1 -queue 20',
                'sort_order' => 3,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'step'       => 4,
                'badge'      => 'Bước 4: Pause 1.5s',
                'title'      => 'Thời gian nghỉ giữa các lần quét',
                'desc'       => 'Tạm dừng 1500ms (1.5 giây) giữa 2 lần quét',
                'cmd'        => '<cmd>rk -pause 1500',
                'sort_order' => 4,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'step'       => 5,
                'badge'      => 'Bước 5: Screen Timeout',
                'title'      => 'Màn hình luôn sáng',
                'desc'       => 'Timeout = 0: Không bao giờ tắt màn hình máy quét',
                'cmd'        => '<cmd>screen -timeout 0',
                'sort_order' => 5,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('qr_config_commands')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_config_commands');
    }
};
