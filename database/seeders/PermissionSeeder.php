<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
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

            // Supply permissions
            'view supply',
            'add supply',
            'edit supply',
            'delete supply',

            // Customer permissions
            'view customer',
            'add customer',
            'edit customer',
            'delete customer',

            'view acrylic order',
            'add acrylic order',
            'edit acrylic order',
            'delete acrylic order',
            
            // Manufacture permissions
            'view manufacture',
            'add manufacture',
            'edit manufacture',
            'delete manufacture',
            'approve manufacture',
            
            // UI permissions
            'view ui',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
