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
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        $adminRole->givePermissionTo([
            // Role permissions
            'view role',
            'add role',
            'edit role',
            'delete role',
            'approve role',
            
            // User permissions
            'view user',
            'add user',
            'edit user',
            'delete user',
            'approve user',
            
            // Media permissions
            'view media',
            'add media',
            'delete media',
        ]);
    }
}
