<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrConfigCommand extends Model
{
    protected $table = 'qr_config_commands';

    protected $fillable = [
        'step',
        'badge',
        'title',
        'desc',
        'cmd',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'step'       => 'integer',
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    /**
     * Default 5 commands definition
     */
    public static function defaultCommands(): array
    {
        return [
            [
                'step'       => 1,
                'badge'      => 'Bước 1: Reset',
                'title'      => 'Khôi phục cài đặt gốc',
                'desc'       => 'Đặt lại toàn bộ thông số máy quét về mặc định ban đầu',
                'cmd'        => '<cmd>rk_reset',
                'sort_order' => 1,
                'is_active'  => true,
            ],
            [
                'step'       => 2,
                'badge'      => 'Bước 2: Wi-Fi',
                'title'      => 'Kết nối Wi-Fi xưởng',
                'desc'       => 'SSID: "CTY GERVIN - XUONG" | Pass: "68686868"',
                'cmd'        => '<cmd>wifi -ssid "CTY GERVIN - XUONG" -pass "68686868"',
                'sort_order' => 2,
                'is_active'  => true,
            ],
            [
                'step'       => 3,
                'badge'      => 'Bước 3: Server',
                'title'      => 'Cấu hình Máy chủ Server',
                'desc'       => 'URL: https://gervinwood.vn/scan -dup 1 -queue 20',
                'cmd'        => '<cmd>server -url "https://gervinwood.vn/scan" -dup 1 -queue 20',
                'sort_order' => 3,
                'is_active'  => true,
            ],
            [
                'step'       => 4,
                'badge'      => 'Bước 4: Pause 1.5s',
                'title'      => 'Thời gian nghỉ giữa các lần quét',
                'desc'       => 'Tạm dừng 1500ms (1.5 giây) giữa 2 lần quét',
                'cmd'        => '<cmd>rk -pause 1500',
                'sort_order' => 4,
                'is_active'  => true,
            ],
            [
                'step'       => 5,
                'badge'      => 'Bước 5: Screen Timeout',
                'title'      => 'Màn hình luôn sáng',
                'desc'       => 'Timeout = 0: Không bao giờ tắt màn hình máy quét',
                'cmd'        => '<cmd>screen -timeout 0',
                'sort_order' => 5,
                'is_active'  => true,
            ],
        ];
    }
}
