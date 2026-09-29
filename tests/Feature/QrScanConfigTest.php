<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrScanConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_scans_page_displays_configuration_commands()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('processes.qr-scans'));

        $response->assertStatus(200);
        $response->assertSee('Quét từ trên xuống để cấu hình Máy quét');
        $response->assertSee('&lt;cmd&gt;rk_reset', false);
        $response->assertSee('&lt;cmd&gt;wifi -ssid &quot;CTY GERVIN - XUONG&quot; -pass &quot;68686868&quot;', false);
        $response->assertSee('&lt;cmd&gt;server -url &quot;https://gervinwood.vn/scan&quot; -dup 1 -queue 20', false);
        $response->assertSee('&lt;cmd&gt;rk -pause 1500', false);
        $response->assertSee('&lt;cmd&gt;screen -timeout 0', false);

        // Kiểm tra hiển thị 3 tab
        $response->assertSee('Cấu hình máy quét');
        $response->assertSee('Danh sách máy');
        $response->assertSee('Nhật ký quét');

        // Kiểm tra cột mã quét gần nhất
        $response->assertSee('MÃ QUÉT GẦN NHẤT');
    }

    public function test_device_list_shows_latest_scanned_barcode()
    {
        $user = User::factory()->create();

        $device = \App\Models\QrDevice::create([
            'id'           => 998877,
            'name'         => 'Máy quét Test Xưởng 1',
            'process_step' => 'cnc',
            'action_type'  => 'complete',
            'is_active'    => true,
        ]);

        \App\Models\QrScanLog::create([
            'device_id'      => $device->id,
            'barcode'        => 'DH001-CANH-01',
            'scanned_at'     => now()->subMinutes(10),
            'scanned_at_raw' => '29-09-2026 14:00:00.000',
            'status'         => 'success',
            'message'        => 'OK 1',
        ]);

        \App\Models\QrScanLog::create([
            'device_id'      => $device->id,
            'barcode'        => 'DH001-CANH-02-LATEST',
            'scanned_at'     => now(),
            'scanned_at_raw' => '29-09-2026 14:10:00.000',
            'status'         => 'success',
            'message'        => 'OK 2',
        ]);

        $response = $this->actingAs($user)->get(route('processes.qr-scans', ['tab' => 'devices']));

        $response->assertStatus(200);
        $response->assertSee('Máy quét Test Xưởng 1');
        $response->assertSee('DH001-CANH-02-LATEST');
    }

    public function test_can_update_qr_configuration_commands()
    {
        $user = User::factory()->create();

        $updatedCommands = [
            [
                'step'  => 1,
                'badge' => 'Bước 1: Reset Máy',
                'title' => 'Khôi phục cài đặt xuất xưởng',
                'desc'  => 'Reset toàn bộ về mặc định',
                'cmd'   => '<cmd>rk_reset',
            ],
            [
                'step'  => 2,
                'badge' => 'Bước 2: Wi-Fi Mới',
                'title' => 'Kết nối Wi-Fi Văn phòng',
                'desc'  => 'SSID: "GERVIN_OFFICE" | Pass: "99999999"',
                'cmd'   => '<cmd>wifi -ssid "GERVIN_OFFICE" -pass "99999999"',
            ],
            [
                'step'  => 3,
                'badge' => 'Bước 3: Server Mới',
                'title' => 'Cấu hình Server ERP',
                'desc'  => 'URL máy chủ nhận mã',
                'cmd'   => '<cmd>server -url "https://gervinwood.vn/api/scan" -dup 1 -queue 30',
            ],
        ];

        $response = $this->actingAs($user)->post(route('processes.qr-scans.update-config-commands'), [
            'commands' => $updatedCommands,
        ]);

        $response->assertRedirect(route('processes.qr-scans', ['tab' => 'config']));
        $response->assertSessionHas('success');

        // Kiểm tra trong database
        $this->assertDatabaseHas('qr_config_commands', [
            'cmd' => '<cmd>wifi -ssid "GERVIN_OFFICE" -pass "99999999"',
            'title' => 'Kết nối Wi-Fi Văn phòng',
        ]);
        $this->assertDatabaseHas('qr_config_commands', [
            'cmd' => '<cmd>server -url "https://gervinwood.vn/api/scan" -dup 1 -queue 30',
        ]);

        // Kiểm tra trang hiển thị nội dung mới
        $getPage = $this->actingAs($user)->get(route('processes.qr-scans'));
        $getPage->assertSee('Kết nối Wi-Fi Văn phòng');
        $getPage->assertSee('&lt;cmd&gt;wifi -ssid &quot;GERVIN_OFFICE&quot; -pass &quot;99999999&quot;', false);
    }

    public function test_can_reset_qr_configuration_commands_to_default()
    {
        $user = User::factory()->create();

        // Đổi cấu hình trước
        \App\Models\QrConfigCommand::truncate();
        \App\Models\QrConfigCommand::create([
            'step' => 1,
            'badge' => 'Custom',
            'title' => 'Custom Command',
            'cmd' => '<cmd>custom_test',
        ]);

        // Thực hiện reset
        $response = $this->actingAs($user)->post(route('processes.qr-scans.reset-config-commands'));

        $response->assertRedirect(route('processes.qr-scans', ['tab' => 'config']));
        $response->assertSessionHas('success');

        // Kiểm tra database có lại 5 lệnh mặc định
        $this->assertEquals(5, \App\Models\QrConfigCommand::count());
        $this->assertDatabaseHas('qr_config_commands', [
            'cmd' => '<cmd>wifi -ssid "CTY GERVIN - XUONG" -pass "68686868"',
        ]);
    }
}
