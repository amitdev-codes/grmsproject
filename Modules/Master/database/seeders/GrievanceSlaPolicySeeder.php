<?php

namespace Modules\Master\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrievanceSlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('grievance_sla_policies')->updateOrInsert(
            ['code' => 'STANDARD'],
            [
                'name' => 'Standard Roads Directorate Grievance SLA',
                'priority' => null,
                'acknowledgement_hours' => 48,
                'resolution_hours' => 168,
                'use_business_hours' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $policyId = DB::table('grievance_sla_policies')->where('code', 'STANDARD')->value('id');

        foreach ([
            ['escalation_level' => 1, 'breach_after_hours' => 48, 'extension_hours' => 48, 'target_role' => 'zonal_officer', 'requires_manual_review' => false],
            ['escalation_level' => 2, 'breach_after_hours' => 120, 'extension_hours' => 48, 'target_role' => 'regional_head', 'requires_manual_review' => false],
            ['escalation_level' => 3, 'breach_after_hours' => 168, 'extension_hours' => null, 'target_role' => 'director_roads', 'requires_manual_review' => true],
        ] as $rule) {
            DB::table('grievance_escalation_rules')->updateOrInsert(
                ['grievance_sla_policy_id' => $policyId, 'escalation_level' => $rule['escalation_level']],
                [...$rule, 'grievance_sla_policy_id' => $policyId, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
