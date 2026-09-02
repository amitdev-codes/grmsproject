<?php

namespace Modules\Grievance\Database\Seeders;

use Illuminate\Database\Seeder;

class GrievanceDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $this->call([
             CategorySeeder::class,
             GrievanceChannelSeeder::class,

         ]);
    }
}
