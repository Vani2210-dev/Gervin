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
    }
}
