<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSupply;
use App\Models\AcrylicOrderItem;
use App\Models\MinLateOrderItem;
use App\Models\GlassOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerShowSheetsMetersTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'view customer']);
        Permission::create(['name' => 'view order']);
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(['view customer', 'view order']);
    }

    public function test_customer_show_displays_total_sheets_and_total_meters()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $customer = Customer::create([
            'customer_code' => 'KH_TEST_01',
            'name'          => 'Công ty Nội Thất An Phát',
            'phone'         => '0912345678',
            'address'       => '123 Phố Huế, Hà Nội',
            'debt'          => 0,
        ]);

        // 1. Min Late Order
        $minLateOrder = Order::create([
            'order_code'    => 'DH_MIN_01',
            'type'          => 'min_late',
            'customer_id'   => $customer->id,
            'customer_name' => 'Dự án Căn hộ A1',
            'order_date'    => '2026-09-20 09:00:00',
            'total_amount'  => 5000000,
            'status'        => 'in_production',
        ]);
        $supplyMin = OrderSupply::create([
            'order_id'    => $minLateOrder->id,
            'supply_name' => 'Ván MDF chống ẩm 17mm',
            'quantity'    => 2,
        ]);
        MinLateOrderItem::create([
            'order_supply_id'       => $supplyMin->id,
            'quantity'              => 5,
            'straight_paste_length' => 12.5,
            'beveled_length'        => 3.2,
        ]);
        MinLateOrderItem::create([
            'order_supply_id'       => $supplyMin->id,
            'quantity'              => 3,
            'straight_paste_length' => 4.3,
            'vat_moi_length'        => 2.0,
        ]);

        // 2. Acrylic Order
        $acrylicOrder = Order::create([
            'order_code'    => 'DH_ACR_02',
            'type'          => 'acrylic',
            'customer_id'   => $customer->id,
            'customer_name' => 'Dự án Biệt thự B2',
            'order_date'    => '2026-09-22 10:00:00',
            'total_amount'  => 8500000,
            'status'        => 'completed',
        ]);
        $supplyAcr = OrderSupply::create([
            'order_id'    => $acrylicOrder->id,
            'supply_name' => 'Acrylic bóng gương PARC 01',
            'quantity'    => 3,
        ]);
        AcrylicOrderItem::create([
            'order_supply_id' => $supplyAcr->id,
            'product_name'    => 'Cánh trên',
            'quantity'        => 4,
            'molding_length'  => 18.6,
        ]);

        // 3. Glass Order
        $glassOrder = Order::create([
            'order_code'    => 'DH_GLS_03',
            'type'          => 'glass',
            'customer_id'   => $customer->id,
            'customer_name' => 'Tủ áo Cánh kính C3',
            'order_date'    => '2026-09-25 14:00:00',
            'total_amount'  => 6200000,
            'status'        => 'transferred',
        ]);
        $supplyGls = OrderSupply::create([
            'order_id'    => $glassOrder->id,
            'supply_name' => 'Khung nhôm xước & kính trà',
            'quantity'    => 2,
        ]);
        GlassOrderItem::create([
            'order_supply_id' => $supplyGls->id,
            'product_name'    => 'Cánh kính mở',
            'wing_quantity'   => 6,
            'height'          => 2000,
            'width'           => 500,
        ]);

        // Verify Order model accessors directly
        $this->assertEquals(8, $minLateOrder->total_sheets); // 5 + 3
        $this->assertEquals(22.0, $minLateOrder->total_meters); // (12.5 + 3.2) + (4.3 + 2.0) = 22.0

        $this->assertEquals(4, $acrylicOrder->total_sheets);
        $this->assertEquals(18.6, $acrylicOrder->total_meters);

        $this->assertEquals(6, $glassOrder->total_sheets);
        // Perimeter: ((2000 * 2 + 500 * 2) / 1000) * 6 = 5.0m * 6 = 30.0m
        $this->assertEquals(30.0, $glassOrder->total_meters);

        // Verify HTML rendering on customers.show
        $response = $this->actingAs($admin)->get(route('customers.show', $customer));

        $response->assertStatus(200);
        $response->assertSee('Số tấm');
        $response->assertSee('Tổng số mét');
        
        // Assert min_late order displays sheets & meters
        $response->assertSee('DH_MIN_01');
        $response->assertSee('8');
        $response->assertSee('22 m');

        // Assert acrylic order displays sheets & meters
        $response->assertSee('DH_ACR_02');
        $response->assertSee('4');
        $response->assertSee('18,6 m');

        // Assert glass order displays sheets & meters
        $response->assertSee('DH_GLS_03');
        $response->assertSee('6');
        $response->assertSee('30 m');

        // Verify HTML rendering on orders.index
        $indexResponse = $this->actingAs($admin)->get(route('orders.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Số tấm');
        $indexResponse->assertSee('Tổng số mét');
        $indexResponse->assertSee('DH_MIN_01');
        $indexResponse->assertSee('DH_ACR_02');
        $indexResponse->assertSee('DH_GLS_03');
    }
}

