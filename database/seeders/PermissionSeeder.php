<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Chuyển quyền cũ sau khi đổi nghiệp vụ xuất xưởng từ shipped sang dispatch.
        foreach ([
            'view shipped' => 'view dispatch',
            'complete shipped' => 'complete dispatch',
        ] as $oldName => $newName) {
            $oldPermission = Permission::where('name', $oldName)->first();
            if (! $oldPermission) {
                continue;
            }

            $newPermission = Permission::firstOrCreate([
                'name' => $newName,
                'guard_name' => 'web',
            ]);

            foreach ($oldPermission->roles as $role) {
                $role->givePermissionTo($newPermission);
            }

            $oldPermission->delete();
        }

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

            // Order permissions
            'view acrylic order',
            'add acrylic order',
            'edit acrylic order',
            'delete acrylic order',

            'view glass order',
            'add glass order',
            'edit glass order',
            'delete glass order',

            'view min late order',
            'add min late order',
            'edit min late order',
            'delete min late order',
            
            // Manufacture permissions
            'view manufacture',
            'add manufacture',
            'edit manufacture',
            'delete manufacture',
            'approve manufacture',

            // Warehouse permissions
            'view warehouse',
            'add warehouse',
            'edit warehouse',
            'delete warehouse',

            // Process permissions
            'view pressing',
            'complete pressing',

            'view cnc',
            'complete cnc',

            'view edge banding',
            'complete edge banding',

            'view finishing',
            'complete finishing',

            'view qc',
            'complete qc',

            'view packing',
            'add packing',
            'delete packing',
            'complete packing',

            'view dispatch',
            'complete dispatch',

            'view delivery', // Quyền xem trang giao hàng
            'complete delivery', // Quyền hoàn tất giao hàng
            
            // UI permissions
            'view ui',
        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Cấp tất cả quyền cho role Admin
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions(Permission::all());
    }
}
