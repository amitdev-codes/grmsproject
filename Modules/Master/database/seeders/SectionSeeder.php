<?php

namespace Modules\Master\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First get the division IDs
        $northern = DB::table('divisions')->where('code', 'NR')->value('id');
        $central  = DB::table('divisions')->where('code', 'CR')->value('id');
        $southern = DB::table('divisions')->where('code', 'SR')->value('id');

        $sections = [
            // ========== NORTHERN REGION ==========
            [
                'code'        => 'NR-BE',
                'division_id' => $northern,
                'name'        => 'Berea Section',
                'name_st'     => 'Karolo ea Berea',
            ],
            [
                'code'        => 'NR-LE',
                'division_id' => $northern,
                'name'        => 'Leribe Section',
                'name_st'     => 'Karolo ea Leribe',
            ],
            [
                'code'        => 'NR-BB',
                'division_id' => $northern,
                'name'        => 'Butha-Buthe Section',
                'name_st'     => 'Karolo ea Butha-Buthe',
            ],

            // ========== CENTRAL REGION ==========
            [
                'code'        => 'CR-MS',
                'division_id' => $central,
                'name'        => 'Maseru Section',
                'name_st'     => 'Karolo ea Maseru',
            ],
            [
                'code'        => 'CR-LB',
                'division_id' => $central,
                'name'        => 'Lithabaneng / Urban Section',
                'name_st'     => 'Karolo ea Lithabaneng',
            ],

            // ========== SOUTHERN REGION ==========
            [
                'code'        => 'SR-MH',
                'division_id' => $southern,
                'name'        => "Mohale's Hoek Section",
                'name_st'     => "Karolo ea Mohale's Hoek",
            ],
            [
                'code'        => 'SR-MF',
                'division_id' => $southern,
                'name'        => 'Mafeteng Section',
                'name_st'     => 'Karolo ea Mafeteng',
            ],
            [
                'code'        => 'SR-QT',
                'division_id' => $southern,
                'name'        => 'Quthing Section',
                'name_st'     => 'Karolo ea Quthing',
            ],
            [
                'code'        => 'SR-QN',
                'division_id' => $southern,
                'name'        => "Qacha's Nek Section",
                'name_st'     => "Karolo ea Qacha's Nek",
            ],
            [
                'code'        => 'SR-TT',
                'division_id' => $southern,
                'name'        => 'Thaba-Tseka Section',
                'name_st'     => 'Karolo ea Thaba-Tseka',
            ],
        ];

        foreach ($sections as $section) {
            DB::table('sections')->updateOrInsert(
                ['code' => $section['code']],
                array_merge($section, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
