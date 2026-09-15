<?php

namespace Modules\Master\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'CONSTRUCTION', 'name' => 'Construction Project', 'name_st' => 'Morero oa Kaho'],
            ['code' => 'REHABILITATION', 'name' => 'Rehabilitation Project', 'name_st' => 'Morero oa Tokiso'],
            ['code' => 'REGRAVELLING', 'name' => 'Regravelling Project', 'name_st' => 'Morero oa ho Kenya Lehlohlojane'],
            ['code' => 'ROUTINE_MAINT', 'name' => 'Routine Maintenance Project', 'name_st' => 'Morero oa Tlhokomelo ea Kamehla'],
            ['code' => 'BRIDGE_CONSTR', 'name' => 'Bridge Construction Project', 'name_st' => 'Morero oa Kaho ea Borokho'],
            ['code' => 'BRIDGE_MAINT', 'name' => 'Bridge Maintenance Project', 'name_st' => 'Morero oa Tlhokomelo ea Borokho'],
            ['code' => 'FOOTBRIDGE_CON', 'name' => 'Footbridge Construction Project', 'name_st' => 'Morero oa Kaho ea Borokho ba Maoto'],
            ['code' => 'FOOTBRIDGE_MNT', 'name' => 'Footbridge Maintenance Project', 'name_st' => 'Morero oa Tlhokomelo ea Borokho ba Maoto'],
            ['code' => 'ROAD_MARKING', 'name' => 'Road Marking Project', 'name_st' => 'Morero oa Matšoao a Tseleng'],
        ];

        foreach ($types as $sortOrder => $type) {
            DB::table('project_types')->updateOrInsert(
                ['code' => $type['code']],
                [...$type, 'sort_order' => $sortOrder, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
