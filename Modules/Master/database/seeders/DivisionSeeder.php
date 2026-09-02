<?php

namespace Modules\Master\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            [
                'code'        => 'NR',
                'name'        => 'Northern Region',
                'name_st'     => 'Sebaka sa Leboea',
                'description' => 'Northern Regional Office – based in Teya-teyaneng (Berea). Covers Berea, Leribe and Butha-Buthe districts.',
            ],
            [
                'code'        => 'CR',
                'name'        => 'Central Region',
                'name_st'     => 'Sebaka sa Bohareng',
                'description' => 'Central Regional Office – based in Lithabaneng (Maseru). Primarily covers Maseru District and surrounding areas.',
            ],
            [
                'code'        => 'SR',
                'name'        => 'Southern Region',
                'name_st'     => 'Sebaka sa Boroa',
                'description' => 'Southern Regional Office – based in Mohale’s Hoek. Covers Mohale’s Hoek, Mafeteng, Quthing, Qacha’s Nek and parts of Thaba-Tseka.',
            ],
        ];

        foreach ($divisions as $division) {
            DB::table('divisions')->updateOrInsert(
                ['code' => $division['code']],
                array_merge($division, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
