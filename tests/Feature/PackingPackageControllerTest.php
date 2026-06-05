<?php

namespace Tests\Feature;

use App\Models\AcrylicOrderItem;
use App\Models\AcrylicOrderItemCode;
use App\Models\Order;
use App\Models\OrderSupply;
use App\Models\PackingPackage;
use App\Models\PackingPackageItem;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackingPackageControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions
        $this->seed(PermissionSeeder::class);

        // Create user with Admin role to have all permissions
        $this->user = User::factory()->create();
        $this->user->assignRole('Admin');
    }

    public function test_can_create_packing_package(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('processes.packing.store'), [
                'name' => 'Package Test 1',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('packing_packages', [
            'name' => 'Package Test 1',
            'status' => 'draft',
        ]);
    }

    public function test_scan_item_code_attaches_all_plates_of_order(): void
    {
        // 1. Create a packing package
        $package = PackingPackage::create([
            'name' => 'Package Test 2',
            'status' => 'draft',
            'packed_by' => $this->user->id,
        ]);

        // 2. Create an Order with 2 Acrylic Plates
        $order = Order::create([
            'order_code' => 'ORD-1234',
            'customer_name' => 'John Doe',
        ]);

        $orderSupply = OrderSupply::create([
            'order_id' => $order->id,
            'supply_name' => 'Acrylic Materials',
        ]);

        $item1 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 1',
            'quantity' => 1,
        ]);

        $code1 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item1->id,
            'product_id' => 'QR-PLATE-1',
            'status' => [],
        ]);

        $item2 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 2',
            'quantity' => 1,
        ]);

        $code2 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item2->id,
            'product_id' => 'QR-PLATE-2',
            'status' => [],
        ]);

        // 3. Scan QR-PLATE-1 (expects both QR-PLATE-1 and QR-PLATE-2 to be added, with QR-PLATE-1 packaged=true, QR-PLATE-2 packaged=false)
        $response = $this->actingAs($this->user)
            ->postJson(route('processes.packing.items.store', $package), [
                'product_code' => 'QR-PLATE-1',
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        // Assert items in database
        $this->assertDatabaseHas('packing_package_items', [
            'packing_package_id' => $package->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-1',
            'is_packaged' => true,
        ]);

        $this->assertDatabaseHas('packing_package_items', [
            'packing_package_id' => $package->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-2',
            'is_packaged' => false,
        ]);
    }

    public function test_scan_second_item_updates_packaged_status(): void
    {
        $package = PackingPackage::create([
            'name' => 'Package Test 3',
            'status' => 'draft',
            'packed_by' => $this->user->id,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-1234',
            'customer_name' => 'John Doe',
        ]);

        $orderSupply = OrderSupply::create([
            'order_id' => $order->id,
            'supply_name' => 'Acrylic Materials',
        ]);

        $item1 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 1',
            'quantity' => 1,
        ]);

        $code1 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item1->id,
            'product_id' => 'QR-PLATE-1',
            'status' => [],
        ]);

        $item2 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 2',
            'quantity' => 1,
        ]);

        $code2 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item2->id,
            'product_id' => 'QR-PLATE-2',
            'status' => [],
        ]);

        // Pre-create items (simulating they were added, QR-PLATE-1 is scanned/packaged, QR-PLATE-2 is not)
        PackingPackageItem::create([
            'packing_package_id' => $package->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-1',
            'is_packaged' => true,
        ]);

        PackingPackageItem::create([
            'packing_package_id' => $package->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-2',
            'is_packaged' => false,
        ]);

        // Scan QR-PLATE-2
        $response = $this->actingAs($this->user)
            ->postJson(route('processes.packing.items.store', $package), [
                'product_code' => 'QR-PLATE-2',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('packing_package_items', [
            'packing_package_id' => $package->id,
            'item_code_id' => 'QR-PLATE-2',
            'is_packaged' => true,
        ]);
    }

    public function test_cannot_scan_if_already_packaged_elsewhere(): void
    {
        $package1 = PackingPackage::create([
            'name' => 'Package Test 4A',
            'status' => 'draft',
            'packed_by' => $this->user->id,
        ]);

        $package2 = PackingPackage::create([
            'name' => 'Package Test 4B',
            'status' => 'draft',
            'packed_by' => $this->user->id,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-1234',
            'customer_name' => 'John Doe',
        ]);

        $orderSupply = OrderSupply::create([
            'order_id' => $order->id,
            'supply_name' => 'Acrylic Materials',
        ]);

        $item1 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 1',
            'quantity' => 1,
        ]);

        $code1 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item1->id,
            'product_id' => 'QR-PLATE-1',
            'status' => [],
        ]);

        // Pack it in Package 1 first
        PackingPackageItem::create([
            'packing_package_id' => $package1->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-1',
            'is_packaged' => true,
        ]);

        // Try to scan in Package 2 -> should fail
        $response = $this->actingAs($this->user)
            ->postJson(route('processes.packing.items.store', $package2), [
                'product_code' => 'QR-PLATE-1',
            ]);

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
    }

    public function test_delete_cleans_up_placeholders_if_no_more_scanned_items_in_order(): void
    {
        $package = PackingPackage::create([
            'name' => 'Package Test 5',
            'status' => 'draft',
            'packed_by' => $this->user->id,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-1234',
            'customer_name' => 'John Doe',
        ]);

        $orderSupply = OrderSupply::create([
            'order_id' => $order->id,
            'supply_name' => 'Acrylic Materials',
        ]);

        $item1 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 1',
            'quantity' => 1,
        ]);

        $code1 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item1->id,
            'product_id' => 'QR-PLATE-1',
            'status' => [],
        ]);

        $item2 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 2',
            'quantity' => 1,
        ]);

        $code2 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item2->id,
            'product_id' => 'QR-PLATE-2',
            'status' => [],
        ]);

        $pkgItem1 = PackingPackageItem::create([
            'packing_package_id' => $package->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-1',
            'is_packaged' => true,
        ]);

        $pkgItem2 = PackingPackageItem::create([
            'packing_package_id' => $package->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-2',
            'is_packaged' => false,
        ]);

        // Delete pkgItem1. Since no other item of this order is packaged (is_packaged=true), pkgItem2 (placeholder) should also be deleted automatically.
        $response = $this->actingAs($this->user)
            ->deleteJson(route('processes.packing.items.destroy', ['package' => $package, 'item' => $pkgItem1]));

        $response->assertOk();

        $this->assertDatabaseMissing('packing_package_items', [
            'id' => $pkgItem1->id,
        ]);

        $this->assertDatabaseMissing('packing_package_items', [
            'id' => $pkgItem2->id,
        ]);
    }

    public function test_delete_does_not_clean_up_placeholders_if_other_scanned_items_exist_in_order(): void
    {
        $package = PackingPackage::create([
            'name' => 'Package Test 6',
            'status' => 'draft',
            'packed_by' => $this->user->id,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-1234',
            'customer_name' => 'John Doe',
        ]);

        $orderSupply = OrderSupply::create([
            'order_id' => $order->id,
            'supply_name' => 'Acrylic Materials',
        ]);

        $item1 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 1',
            'quantity' => 1,
        ]);

        $code1 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item1->id,
            'product_id' => 'QR-PLATE-1',
            'status' => [],
        ]);

        $item2 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 2',
            'quantity' => 1,
        ]);

        $code2 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item2->id,
            'product_id' => 'QR-PLATE-2',
            'status' => [],
        ]);

        $item3 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply->id,
            'product_name' => 'Plate 3',
            'quantity' => 1,
        ]);

        $code3 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item3->id,
            'product_id' => 'QR-PLATE-3',
            'status' => [],
        ]);

        $pkgItem1 = PackingPackageItem::create([
            'packing_package_id' => $package->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-1',
            'is_packaged' => true,
        ]);

        $pkgItem2 = PackingPackageItem::create([
            'packing_package_id' => $package->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-2',
            'is_packaged' => true,
        ]);

        $pkgItem3 = PackingPackageItem::create([
            'packing_package_id' => $package->id,
            'item_code_type' => AcrylicOrderItemCode::class,
            'item_code_id' => 'QR-PLATE-3',
            'is_packaged' => false,
        ]);

        // Delete pkgItem1. Since pkgItem2 is still is_packaged=true for the same order, pkgItem3 (placeholder) should remain intact.
        $response = $this->actingAs($this->user)
            ->deleteJson(route('processes.packing.items.destroy', ['package' => $package, 'item' => $pkgItem1]));

        $response->assertOk();

        $this->assertDatabaseMissing('packing_package_items', [
            'id' => $pkgItem1->id,
        ]);

        $this->assertDatabaseHas('packing_package_items', [
            'id' => $pkgItem2->id,
            'is_packaged' => true,
        ]);

        $this->assertDatabaseHas('packing_package_items', [
            'id' => $pkgItem3->id,
            'is_packaged' => false,
        ]);
    }

    public function test_cannot_scan_plates_of_different_orders_into_same_package(): void
    {
        $package = PackingPackage::create([
            'name' => 'Package Test Single Order Constraint',
            'status' => 'draft',
            'packed_by' => $this->user->id,
        ]);

        // Order 1
        $order1 = Order::create([
            'order_code' => 'ORD-1',
            'customer_name' => 'John Doe',
        ]);
        $orderSupply1 = OrderSupply::create([
            'order_id' => $order1->id,
            'supply_name' => 'Acrylic Materials',
        ]);
        $item1 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply1->id,
            'product_name' => 'Plate 1',
            'quantity' => 1,
        ]);
        $code1 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item1->id,
            'product_id' => 'QR-PLATE-1',
            'status' => [],
        ]);

        // Order 2
        $order2 = Order::create([
            'order_code' => 'ORD-2',
            'customer_name' => 'Jane Smith',
        ]);
        $orderSupply2 = OrderSupply::create([
            'order_id' => $order2->id,
            'supply_name' => 'Acrylic Materials 2',
        ]);
        $item2 = AcrylicOrderItem::create([
            'order_supply_id' => $orderSupply2->id,
            'product_name' => 'Plate 2',
            'quantity' => 1,
        ]);
        $code2 = AcrylicOrderItemCode::create([
            'acrylic_order_item_id' => $item2->id,
            'product_id' => 'QR-PLATE-2',
            'status' => [],
        ]);

        // Scan Order 1 item first
        $this->actingAs($this->user)
            ->postJson(route('processes.packing.items.store', $package), [
                'product_code' => 'QR-PLATE-1',
            ])->assertOk();

        // Try to scan Order 2 item in the same package -> should fail
        $response = $this->actingAs($this->user)
            ->postJson(route('processes.packing.items.store', $package), [
                'product_code' => 'QR-PLATE-2',
            ]);

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
        $response->assertJsonFragment([
            'message' => 'Kiện này đã chứa linh kiện của đơn hàng khác. Mỗi kiện chỉ được chứa tối đa 1 đơn hàng.',
        ]);
    }
}
