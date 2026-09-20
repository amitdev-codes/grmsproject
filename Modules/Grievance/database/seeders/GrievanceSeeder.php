<?php

namespace Modules\Grievance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;
use Modules\Master\Models\Section;

class GrievanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grievances = [
            [
                'reference_no' => 'GRM-2026-00001',
                'complainant_name' => 'Mamello Mokoena',
                'category' => 'road-damage',
                'district' => 'MSU',
                'division' => 'MAINT',
                'section' => 'Pothole & Surface Repair',
                'description' => 'Large pothole on the Mafeteng access road is damaging vehicles and causing traffic to swerve into oncoming lanes.',
                'status' => 'in_progress',
                'priority' => 'high',
            ],
            [
                'reference_no' => 'GRM-2026-00002',
                'complainant_name' => null,
                'category' => 'safety-hazard',
                'district' => 'BER',
                'division' => null,
                'section' => null,
                'description' => 'No warning signage at an active roadworks site near Teyateyaneng at night.',
                'status' => 'submitted',
                'priority' => 'high',
            ],
            [
                'reference_no' => 'GRM-2026-00003',
                'complainant_name' => 'Mamello Mokoena',
                'category' => 'land-resettlement',
                'district' => 'LRB',
                'division' => 'SAFE',
                'section' => 'Land Acquisition & Compensation',
                'description' => 'Compensation for land acquired under the LITTL road-widening works has not been received.',
                'status' => 'allocated_division',
                'priority' => 'normal',
            ],
            [
                'reference_no' => 'GRM-2026-00004',
                'complainant_name' => 'Thabo Ndlovu',
                'category' => 'road-damage',
                'district' => 'MSU',
                'division' => 'MAINT',
                'section' => 'Pothole & Surface Repair',
                'description' => 'The same road section has developed additional potholes after recent rainfall.',
                'status' => 'allocated_section',
                'priority' => 'normal',
            ],
        ];
        foreach ($grievances as $g) {
            Grievance::updateOrCreate(
                ['reference_no' => $g['reference_no']],
                [
                    'grievance_category_id' => GrievanceCategory::where('slug', $g['category'])->value('id'),
                    'channel_id' => DB::table('grievance_channels')->where('code', 'web')->value('id'),
                    'district_id' => District::where('code', $g['district'])->value('id'),
                    'division_id' => $g['division'] ? Division::where('code', $g['division'])->value('id') : null,
                    'section_id' => $g['section'] ? Section::where('name', $g['section'])->value('id') : null,
                    'complainant_name' => $g['complainant_name'],
                    'is_anonymous' => is_null($g['complainant_name']),
                    'description' => $g['description'],
                    'status' => $g['status'],
                    'priority' => $g['priority'],
                    'registered_by' => DB::table('users')->where('email', 'helpdesk@grms.com')->value('id'),
                    'sla_due_at' => now()->addDays(5),
                    'created_at' => now()->subDays(rand(1, 4)),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
