<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WoodBoard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WarehouseEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'view warehouse']);
        Permission::firstOrCreate(['name' => 'add warehouse']);
        Permission::firstOrCreate(['name' => 'edit warehouse']);
        Permission::firstOrCreate(['name' => 'delete warehouse']);

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(['view warehouse', 'add warehouse', 'edit warehouse', 'delete warehouse']);
    }

    public function test_warehouse_list_screen_renders_edit_button_and_updates_warehouse()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $warehouse = Warehouse::create([
            'name'        => 'Kho Gỗ Cốt MDF',
            'code'        => 'KHO-MDF',
            'manager'     => 'Nguyễn Văn Kho',
            'address'     => 'Xưởng 1, KCN Hưng Nguyên',
            'status'      => 'active',
            'description' => 'Kho lưu ván MDF chống ẩm',
        ]);

        // 1. Check GET /warehouses/list screen
        $response = $this->actingAs($admin)->get(route('inventory.warehouses.index'));
        $response->assertStatus(200);
        $response->assertSee('Kho Gỗ Cốt MDF');
        $response->assertSee('data-warehouse=', false);
        $response->assertSee('openEditWarehouseModal(this)', false);

        // 2. Test updating warehouse via PUT /warehouses/list/{warehouse}
        $updateResponse = $this->actingAs($admin)->put(route('inventory.warehouses.update', $warehouse), [
            'name'        => 'Kho Gỗ Cốt MDF Cập Nhật',
            'code'        => 'KHO-MDF-01',
            'manager'     => 'Trần Thủ Kho',
            'address'     => 'Xưởng 2, KCN Hưng Nguyên',
            'status'      => 'inactive',
            'description' => 'Đã cập nhật thông tin kho',
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $warehouse->refresh();
        $this->assertEquals('Kho Gỗ Cốt MDF Cập Nhật', $warehouse->name);
        $this->assertEquals('KHO-MDF-01', $warehouse->code);
        $this->assertEquals('Trần Thủ Kho', $warehouse->manager);
        $this->assertEquals('inactive', $warehouse->status);
    }

    public function test_materials_screen_renders_edit_button_and_updates_material()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $warehouse = Warehouse::create([
            'name'   => 'Kho Acrylic',
            'code'   => 'KHO-ACR',
            'status' => 'active',
        ]);

        $board = WoodBoard::create([
            'color_code'    => 'PARC01',
            'origin_code'   => 'ACR-8801',
            'price_group'   => 'Acrylic Bóng Gương',
            'unit'          => 'Tấm',
            'cost_price'    => 450000,
            'min_stock'     => 20,
            'current_stock' => 50,
            'warehouse_id'  => $warehouse->id,
            'status'        => 'active',
        ]);

        // 1. Check GET /warehouses
        $response = $this->actingAs($admin)->get(route('inventory.index'));
        $response->assertStatus(200);
        $response->assertSee('PARC01');
        $response->assertSee('data-material=', false);
        $response->assertSee('editMaterial(this)', false);

        // 2. Test updating material via PUT /warehouses/materials/{material}
        $updateResponse = $this->actingAs($admin)->put(route('inventory.materials.update', $board), [
            'origin_code'  => 'ACR-9999',
            'category'     => 'Acrylic Cao Cấp',
            'unit'         => 'Tấm',
            'cost_price'   => 480000,
            'min_stock'    => 30,
            'warehouse_id' => $warehouse->id,
            'status'       => 'active',
            'notes'        => 'Đã điều chỉnh giá vốn',
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $board->refresh();
        $this->assertEquals('ACR-9999', $board->origin_code);
        $this->assertEquals('Acrylic Cao Cấp', $board->price_group);
        $this->assertEquals(480000, $board->cost_price);
        $this->assertEquals(30, $board->min_stock);
        $this->assertEquals('Đã điều chỉnh giá vốn', $board->notes);
    }
}
