<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WoodBoard;
use App\Models\InventoryTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryReportWarehouseFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Warehouse $warehouseA;
    protected Warehouse $warehouseB;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'view warehouse']);
        Permission::firstOrCreate(['name' => 'view report']);

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(['view warehouse', 'view report']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->warehouseA = Warehouse::create([
            'name' => 'Kho Hà Nội',
            'code' => 'KHO-HN',
            'status' => 'active',
        ]);

        $this->warehouseB = Warehouse::create([
            'name' => 'Kho Sài Gòn',
            'code' => 'KHO-SG',
            'status' => 'active',
        ]);
    }

    public function test_reports_screen_renders_warehouse_filter_options()
    {
        $response = $this->actingAs($this->admin)->get(route('inventory.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Kho Hà Nội');
        $response->assertSee('Kho Sài Gòn');
        $response->assertSee('name="warehouse_id"', false);
    }

    public function test_reports_summary_filters_by_selected_warehouse()
    {
        // Board A in Warehouse A
        $boardA = WoodBoard::create([
            'color_code' => 'VAN-HN-01',
            'price_group' => 'MDF 17mm',
            'current_stock' => 10,
            'warehouse_id' => $this->warehouseA->id,
            'unit' => 'Tấm',
        ]);

        // Board B in Warehouse B
        $boardB = WoodBoard::create([
            'color_code' => 'VAN-SG-01',
            'price_group' => 'MFC 18mm',
            'current_stock' => 20,
            'warehouse_id' => $this->warehouseB->id,
            'unit' => 'Tấm',
        ]);

        // Filter by warehouse A
        $responseA = $this->actingAs($this->admin)->get(route('inventory.reports.index', [
            'tab' => 'summary',
            'warehouse_id' => $this->warehouseA->id,
        ]));
        $responseA->assertStatus(200);
        $responseA->assertSee('VAN-HN-01');
        $responseA->assertDontSee('VAN-SG-01');
        $responseA->assertSee('Đang lọc theo kho: Kho Hà Nội');

        // Filter by warehouse B
        $responseB = $this->actingAs($this->admin)->get(route('inventory.reports.index', [
            'tab' => 'summary',
            'warehouse_id' => $this->warehouseB->id,
        ]));
        $responseB->assertStatus(200);
        $responseB->assertSee('VAN-SG-01');
        $responseB->assertDontSee('VAN-HN-01');
        $responseB->assertSee('Đang lọc theo kho: Kho Sài Gòn');
    }

    public function test_matrix_tab_filters_by_warehouse()
    {
        $boardA = WoodBoard::create([
            'color_code' => 'VAN-HN-02',
            'price_group' => 'MDF 17mm',
            'current_stock' => 15,
            'warehouse_id' => $this->warehouseA->id,
            'unit' => 'Tấm',
        ]);

        $boardB = WoodBoard::create([
            'color_code' => 'VAN-SG-02',
            'price_group' => 'MFC 18mm',
            'current_stock' => 25,
            'warehouse_id' => $this->warehouseB->id,
            'unit' => 'Tấm',
        ]);

        $response = $this->actingAs($this->admin)->get(route('inventory.reports.index', [
            'tab' => 'matrix',
            'year' => date('Y'),
            'warehouse_id' => $this->warehouseA->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('VAN-HN-02');
        $response->assertDontSee('VAN-SG-02');
        $response->assertSee('Đang lọc theo kho: Kho Hà Nội');
    }

    public function test_excel_exports_with_warehouse_filter()
    {
        $summaryExport = $this->actingAs($this->admin)->get(route('inventory.reports.export', [
            'warehouse_id' => $this->warehouseA->id,
        ]));
        $summaryExport->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $summaryExport->headers->get('content-type')
        );

        $matrixExport = $this->actingAs($this->admin)->get(route('inventory.reports.export-matrix', [
            'year' => date('Y'),
            'warehouse_id' => $this->warehouseA->id,
        ]));
        $matrixExport->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $matrixExport->headers->get('content-type')
        );
    }
}
