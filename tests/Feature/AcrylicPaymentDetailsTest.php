<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\PaymentDetail;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcrylicPaymentDetailsTest extends TestCase
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

    public function test_can_save_acrylic_order_with_payment_details(): void
    {
        // 1. Create a draft order
        $draftOrder = Order::create([
            'order_code' => 'DH-TEST-01',
            'type' => 'acrylic',
            'status' => 'draft',
            'customer_name' => 'Draft Customer',
            'total_amount' => 0,
        ]);

        // 2. Submit store (finalize draft) with supplies and payment details
        $response = $this->actingAs($this->admin)->post(route('orders.store'), [
            'draft_order_id' => $draftOrder->id,
            'type' => 'acrylic',
            'customer_name' => 'Final Customer',
            'order_date' => '2026-07-17',
            'delivery_days' => 2,
            'discount_percent' => 10,
            'vat_percent' => 8,
            'supplies' => [
                [
                    'order_supply_code' => 'SUP1',
                    'supply_name' => 'Wood Board 18mm',
                    'quantity' => 1,
                    'items' => [
                        [
                            'product_name' => 'Acrylic Door panel',
                            'quantity' => 2,
                            'unit_price' => 500000, // total = 1,000,000
                            'total_price' => '1.000.000',
                            'thickness' => '18mm',
                            'height' => 1000,
                            'width' => 600,
                        ]
                    ]
                ]
            ],
            'payment_details' => [
                [
                    'name' => 'Extra cutting service',
                    'unit' => 'lần',
                    'quantity' => 5,
                    'price' => 20000, // total = 100,000
                    'total' => '100.000',
                ],
                [
                    'name' => 'Delivery cost',
                    'unit' => 'chuyến',
                    'quantity' => 1,
                    'price' => 150000, // total = 150,000
                    'total' => '150.000',
                ]
            ]
        ]);

        $response->assertRedirect(route('orders.index'));

        // 3. Verify order total calculation
        // Subtotal = supplies items total (1,000,000) + payment details total (100,000 + 150,000) = 1,250,000
        // Discount 10% = 125,000
        // VAT 8% on (1,250,000 - 125,000) = 8% of 1,125,000 = 90,000
        // Total = 1,250,000 - 125,000 + 90,000 = 1,215,000
        $order = Order::find($draftOrder->id);
        $this->assertEquals(1215000, $order->total_amount);

        $this->assertDatabaseHas('payment_details', [
            'order_id' => $order->id,
            'name' => 'Extra cutting service',
            'quantity' => 5,
            'price' => 20000,
            'total' => 100000,
        ]);
        
        $paymentDetails = $order->paymentDetails;
        $this->assertCount(2, $paymentDetails);
        $this->assertEquals(100000, $paymentDetails[0]->total);
        $this->assertEquals(150000, $paymentDetails[1]->total);
    }
}
