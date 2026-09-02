<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;

class UserManagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class
        ]);
    }
}
