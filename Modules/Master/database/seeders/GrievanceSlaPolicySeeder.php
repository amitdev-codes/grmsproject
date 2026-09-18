<?php

namespace Modules\Master\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Master\Models\GrievanceSlaPolicy;
use Modules\Master\Models\GrievanceEscalationRule;

class GrievanceSlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policy = GrievanceSlaPolicy::updateOrCreate(
            ['code' => 'STANDARD'],
            [
                'name' => 'Standard Roads Directorate Grievance SLA',
                'priority' => null,
                'acknowledgement_hours' => 48,
                'resolution_hours' => 168,
                'use_business_hours' => true,
                'is_active' => true,
            ]
        );

        foreach ([
                     ['escalation_level' => 1, 'breach_after_hours' => 48,  'extension_hours' => 48,   'target_role' => 'zonal_officer',  'requires_manual_review' => false],
                     ['escalation_level' => 2, 'breach_after_hours' => 120, 'extension_hours' => 48,   'target_role' => 'regional_head',  'requires_manual_review' => false],
                     ['escalation_level' => 3, 'breach_after_hours' => 168, 'extension_hours' => null, 'target_role' => 'director_roads', 'requires_manual_review' => true],
                 ] as $rule) {
            GrievanceEscalationRule::updateOrCreate(
                [
                    'grievance_sla_policy_id' => $policy->id, // internal FK — stays id, correct
                    'escalation_level' => $rule['escalation_level'],
                ],
                [...$rule, 'is_active' => true]
            );
        }
    }
}
