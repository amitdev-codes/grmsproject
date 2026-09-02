<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Grievance\Database\Seeders\GrievanceDatabaseSeeder;
use Modules\Master\Database\Seeders\MasterDatabaseSeeder;
use Modules\Setting\Database\Seeders\SettingDatabaseSeeder;
use Modules\UserManagement\Database\Seeders\UserManagementDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(MasterDatabaseSeeder::class);
        $this->call(UserManagementDatabaseSeeder::class);
        $this->call(GrievanceDatabaseSeeder::class);
        $this->call(SettingDatabaseSeeder::class);

    }
}
