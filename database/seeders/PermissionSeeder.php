<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view role',
            'add role',
            'edit role',
            'delete role',
            'approve role',
            'view user',
            'add user',
            'edit user',
            'delete user',
            'approve user',
            'view media',
            'add media',
            'delete media',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
