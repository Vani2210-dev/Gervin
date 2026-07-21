<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderSupply;
use App\Models\ManufactureOrder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManufactureExcelExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();

        // Create Admin
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->admin->assignRole('Admin');
    }

    public function test_guest_cannot_export_excel(): void
    {
        $response = $this->get(route('manufactures.export-excel'));
        $response->assertRedirect('/login');
    }

    public function test_admin_can_export_excel(): void
    {
        // 1. Create a sample order and supply
        $order = Order::create([
            'order_code' => 'ORD-9999',
            'customer_name' => 'John Doe',
            'type' => 'acrylic',
            'status' => 'in_production',
            'order_date' => '2026-07-10 10:00:00'
        ]);

        $supply = OrderSupply::create([
            'order_id' => $order->id,
            'supply_name' => 'GV75',
            'quantity' => 1
        ]);

        // 2. Export excel for the date
        $response = $this->actingAs($this->admin)->get(route('manufactures.export-excel', [
            'date' => '2026-07-10'
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="Danh_sach_sap_xep_don_hang_ngay_2026_07_10.xlsx"');
        
        $this->assertNotEmpty($response->getContent());
    }
}
