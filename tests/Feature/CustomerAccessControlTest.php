<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee1;
    private User $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();
        $employeeRole = \Spatie\Permission\Models\Role::where('name', 'Nhân viên')->first();

        // Create Admin
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]); // Admin
        $this->admin->assignRole('Admin');

        // Create Employee 1
        $this->employee1 = User::factory()->create(['role_id' => $employeeRole->id]); // Employee
        $this->employee1->assignRole('Nhân viên');
        $this->employee1->givePermissionTo('view customer', 'add customer', 'edit customer', 'delete customer', 'view order');

        // Create Employee 2
        $this->employee2 = User::factory()->create(['role_id' => $employeeRole->id]); // Employee
        $this->employee2->assignRole('Nhân viên');
        $this->employee2->givePermissionTo('view customer', 'add customer', 'edit customer', 'delete customer', 'view order');
    }

    public function test_admin_can_view_all_customers(): void
    {
        $cust1 = Customer::create(['name' => 'Cust 1', 'customer_code' => 'KH00001']);
        $cust2 = Customer::create(['name' => 'Cust 2', 'customer_code' => 'KH00002']);

        $cust1->users()->sync([$this->employee1->id]);
        $cust2->users()->sync([$this->employee2->id]);

        $response = $this->actingAs($this->admin)->get(route('customers.index'));
        $response->assertOk();
        $response->assertSee('Cust 1');
        $response->assertSee('Cust 2');
    }

    public function test_employee_can_only_view_assigned_customers(): void
    {
        $cust1 = Customer::create(['name' => 'Cust 1', 'customer_code' => 'KH00001']);
        $cust2 = Customer::create(['name' => 'Cust 2', 'customer_code' => 'KH00002']);

        $cust1->users()->sync([$this->employee1->id]);
        $cust2->users()->sync([$this->employee2->id]);

        // Employee 1 view
        $response = $this->actingAs($this->employee1)->get(route('customers.index'));
        $response->assertOk();
        $response->assertSee('Cust 1');
        $response->assertDontSee('Cust 2');

        // Employee 2 view
        $response = $this->actingAs($this->employee2)->get(route('customers.index'));
        $response->assertOk();
        $response->assertDontSee('Cust 1');
        $response->assertSee('Cust 2');
    }

    public function test_employee_creates_customer_is_automatically_assigned(): void
    {
        $response = $this->actingAs($this->employee1)->post(route('customers.store'), [
            'name' => 'New Created Cust',
            'phone' => '1234567890',
        ]);

        $response->assertRedirect(route('customers.index'));
        
        $customer = Customer::where('name', 'New Created Cust')->first();
        $this->assertNotNull($customer);
        $this->assertTrue($customer->users->contains($this->employee1->id));
    }

    public function test_employee_cannot_access_unassigned_customer_overview(): void
    {
        $cust2 = Customer::create(['name' => 'Cust 2', 'customer_code' => 'KH00002']);
        $cust2->users()->sync([$this->employee2->id]);

        $response = $this->actingAs($this->employee1)->get(route('customers.overview', $cust2));
        $response->assertStatus(403);
    }

    public function test_employee_cannot_update_unassigned_customer(): void
    {
        $cust2 = Customer::create(['name' => 'Cust 2', 'customer_code' => 'KH00002']);
        $cust2->users()->sync([$this->employee2->id]);

        $response = $this->actingAs($this->employee1)->put(route('customers.update', $cust2), [
            'name' => 'Updated Cust 2',
        ]);
        $response->assertStatus(403);
    }

    public function test_employee_cannot_delete_unassigned_customer(): void
    {
        $cust2 = Customer::create(['name' => 'Cust 2', 'customer_code' => 'KH00002']);
        $cust2->users()->sync([$this->employee2->id]);

        $response = $this->actingAs($this->employee1)->delete(route('customers.destroy', $cust2));
        $response->assertStatus(403);
    }

    public function test_employee_can_only_view_assigned_customer_orders(): void
    {
        $cust1 = Customer::create(['name' => 'Cust 1', 'customer_code' => 'KH00001']);
        $cust2 = Customer::create(['name' => 'Cust 2', 'customer_code' => 'KH00002']);

        $cust1->users()->sync([$this->employee1->id]);
        $cust2->users()->sync([$this->employee2->id]);

        $order1 = Order::create(['order_code' => 'ORD-1111', 'customer_id' => $cust1->id, 'customer_name' => $cust1->name, 'status' => 'pending']);
        $order2 = Order::create(['order_code' => 'ORD-2222', 'customer_id' => $cust2->id, 'customer_name' => $cust2->name, 'status' => 'pending']);

        // Employee 1 view orders list
        $response = $this->actingAs($this->employee1)->get(route('orders.index'));
        $response->assertOk();
        $response->assertSee('ORD-1111');
        $response->assertDontSee('ORD-2222');

        // Try directly showing order 2
        $response = $this->actingAs($this->employee1)->get(route('orders.show', $order2));
        $response->assertStatus(403);
    }

    public function test_customer_date_range_filtering_and_period_paid(): void
    {
        // July 2026 is current month for mock
        \Carbon\Carbon::setTestNow('2026-07-15');

        $cust1 = Customer::create(['name' => 'John Doe', 'customer_code' => 'KH00001', 'debt' => 25000000]); // 25M
        $cust2 = Customer::create(['name' => 'Jane Smith', 'customer_code' => 'KH00002', 'debt' => 120000000]); // 120M

        $this->admin->customers()->sync([$cust1->id, $cust2->id]);

        // Create payments
        // Payment in July 2026
        \App\Models\CustomerPayment::create([
            'customer_id' => $cust1->id,
            'payment_date' => '2026-07-10',
            'amount' => 200000,
            'payment_method' => 'cash',
        ]);
        // Payment in August 2026
        \App\Models\CustomerPayment::create([
            'customer_id' => $cust1->id,
            'payment_date' => '2026-08-05',
            'amount' => 300000,
            'payment_method' => 'cash',
        ]);

        // 1. Without date range filter (Defaults to current month: 2026-07-01 to 2026-07-31)
        $response = $this->actingAs($this->admin)->get(route('customers.index'));
        
        $response->assertOk();
        $response->assertViewHas('lostCustomersCount', 0);
        $response->assertViewHas('newCustomersCount', 2);

        // John Doe has a payment in July 2026, so he should show up
        $response->assertSee('John Doe');
        // Jane Smith has no transactions in July 2026, so she should be filtered out
        $response->assertDontSee('Jane Smith');
        // Check July payment sum (200,000)
        $response->assertSee('200.000');

        // 2. Filter for August 2026:
        $response = $this->actingAs($this->admin)->get(route('customers.index', [
            'filter_start_date' => '2026-08-01',
            'filter_end_date' => '2026-08-31',
        ]));
        
        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
        $response->assertSee('300.000');

        // 3. Filter by Customer ID:
        $response = $this->actingAs($this->admin)->get(route('customers.index', [
            'filter_customer_id' => $cust1->id,
        ]));
        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');

        // 4. Filter by Debt level (10-30M range contains John Doe - 25M)
        $response = $this->actingAs($this->admin)->get(route('customers.index', [
            'filter_debt_level' => '10-30',
        ]));
        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');

        // 5. Filter by Debt level (100-150M range contains Jane Smith - 120M)
        $response = $this->actingAs($this->admin)->get(route('customers.index', [
            'filter_debt_level' => '100-150',
            'filter_start_date' => '', // ensure we bypass date range constraint
            'filter_end_date' => '',
        ]));
        $response->assertOk();
        $response->assertDontSee('John Doe');
        $response->assertSee('Jane Smith');

        // Reset test time
        \Carbon\Carbon::setTestNow();
    }
}
