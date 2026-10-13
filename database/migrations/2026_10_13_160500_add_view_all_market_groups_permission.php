<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'view all market groups',
            'guard_name' => 'web'
        ]);

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole && !$adminRole->hasPermissionTo('view all market groups')) {
            $adminRole->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::where('name', 'view all market groups')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
