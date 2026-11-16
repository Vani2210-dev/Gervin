<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'Nhân viên',
            'guard_name' => 'web',
        ]);

        $accountantRole = Role::firstOrCreate([
            'name' => 'Kế toán',
            'guard_name' => 'web',
        ]);
        $accountantRole->givePermissionTo(['view order', 'edit order', 'approve accountant order']);

        $technicalRole = Role::firstOrCreate([
            'name' => 'Kỹ thuật',
            'guard_name' => 'web',
        ]);
        $technicalRole->givePermissionTo(['view order', 'edit order', 'view manufacture', 'approve manufacture', 'approve technical order']);

    }
}
