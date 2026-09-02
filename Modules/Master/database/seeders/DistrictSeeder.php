<?php

namespace Modules\Master\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = [
            ['code' => 'BE', 'name' => 'Berea',           'name_st' => 'Setereke sa Berea'],
            ['code' => 'BB', 'name' => 'Butha-Buthe',     'name_st' => 'Setereke sa Butha-Buthe'], // also written Botha-Bothe
            ['code' => 'LE', 'name' => 'Leribe',          'name_st' => 'Setereke sa Leribe'],
            ['code' => 'MF', 'name' => 'Mafeteng',        'name_st' => 'Setereke sa Mafeteng'],
            ['code' => 'MS', 'name' => 'Maseru',          'name_st' => 'Setereke sa Maseru'],
            ['code' => 'MH', 'name' => "Mohale's Hoek",   'name_st' => "Setereke sa Mohale's Hoek"],
            ['code' => 'MK', 'name' => 'Mokhotlong',      'name_st' => 'Setereke sa Mokhotlong'],
            ['code' => 'QN', 'name' => "Qacha's Nek",     'name_st' => "Setereke sa Qacha's Nek"],
            ['code' => 'QT', 'name' => 'Quthing',         'name_st' => 'Setereke sa Quthing'],
            ['code' => 'TT', 'name' => 'Thaba-Tseka',     'name_st' => 'Setereke sa Thaba-Tseka'],
        ];

        foreach ($districts as $district) {
            DB::table('districts')->updateOrInsert(
                ['code' => $district['code']],
                array_merge($district, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
