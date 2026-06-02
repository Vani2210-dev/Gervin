<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'admin@kbtech.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        )->assignRole('Quản trị viên');

        User::firstOrCreate(
            ['email' => 'test@kbtech.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        )->assignRole('Nhân viên');
    }
}
