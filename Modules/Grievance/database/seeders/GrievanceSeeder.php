<?php

namespace Modules\Grievance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrievanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grievances = [
            [
                'reference_number' => 'GRM-2026-00001',
                'user_id' => DB::table('users')->where('email', 'mamello@example.com')->value('id'),
                'category' => 'road-damage',
                'district' => 'MSU',
                'division' => 'MAINT',
                'section' => 'Pothole & Surface Repair',
                'officer' => DB::table('users')->where('email', 'officer.maseru@roads.gov.ls')->value('id'),
                'description' => 'Large pothole on the Mafeteng access road is damaging vehicles and causing traffic to swerve into oncoming lanes.',
                'status' => 'in_progress',
                'priority' => 'high',
            ],
            [
                'reference_number' => 'GRM-2026-00002',
                'user_id' => null,
                'category' => 'safety-hazard',
                'district' => 'BER',
                'division' => 'MAINT',
                'section' => null,
                'officer' => null,
                'description' => 'No warning signage at an active roadworks site near Teyateyaneng at night.',
                'status' => 'submitted',
                'priority' => 'high',
            ],
            [
                'reference_number' => 'GRM-2026-00003',
                'user_id' => DB::table('users')->where('email', 'mamello@example.com')->value('id'),
                'category' => 'land-resettlement',
                'district' => 'LRB',
                'division' => 'SAFE',
                'section' => 'Land Acquisition & Compensation',
                'officer' => null,
                'description' => 'Compensation for land acquired under the LITTL road-widening works has not been received.',
                'status' => 'allocated',
                'priority' => 'normal',
            ],
        ];
        foreach ($grievances as $g) {
            DB::table('grievances')->updateOrInsert(
                ['reference_number' => $g['reference_number']],
                [
                    'reference_number' => $g['reference_number'],
                    'user_id' => $g['user_id'],
                    'is_anonymous' => is_null($g['user_id']),
                    'category_id' => DB::table('grievance_categories')->where('slug', $g['category'])->value('id'),
                    'district_id' => DB::table('districts')->where('code', $g['district'])->value('id'),
                    'division_id' => DB::table('divisions')->where('name', $g['division'])->value('id'),
                    'section_id' => $g['section'] ? DB::table('sections')->where('name', $g['section'])->value('id') : null,
                    'assigned_officer_id' => $g['officer'],
                    'description' => $g['description'],
                    'status' => $g['status'],
                    'priority' => $g['priority'],
                    'submitted_via' => 'web',
                    'sla_due_at' => now()->addDays(5),
                    'created_at' => now()->subDays(rand(1, 4)),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
