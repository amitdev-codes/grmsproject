<?php

namespace Modules\Grievance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrievanceWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            [
                'step_number' => 1,
                'name' => 'Initial review and allocation',
                'description' => 'Reviews the registered grievance and allocates it to a division.',
                'action_type' => 'allocate_division',
                'role_name' => 'Director',
                'approval_action' => 'advance',
                'rejection_action' => 'reject',
            ],
            [
                'step_number' => 2,
                'name' => 'Division review',
                'description' => 'Reviews the grievance and allocates it to a section.',
                'action_type' => 'allocate_section',
                'role_name' => 'Division Director',
                'approval_action' => 'advance',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 1,
            ],
            [
                'step_number' => 3,
                'name' => 'Section review',
                'description' => 'Reviews the grievance and assigns an investigating officer.',
                'action_type' => 'assign_officer',
                'role_name' => 'Section Manager',
                'approval_action' => 'advance',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 2,
            ],
            [
                'step_number' => 4,
                'name' => 'Investigation and proposed resolution',
                'description' => 'Investigates the grievance and submits a proposed resolution.',
                'action_type' => 'prepare_resolution',
                'role_name' => 'Helpdesk Officer',
                'approval_action' => 'advance',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 3,
            ],
            [
                'step_number' => 5,
                'name' => 'Final approval',
                'description' => 'Approves the proposed resolution or returns it for further investigation.',
                'action_type' => 'final_approval',
                'role_name' => 'Director',
                'approval_action' => 'resolve',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 4,
                'is_final_approval' => true,
            ],
        ];

        foreach ($steps as $step) {
            $workflowStep = DB::table('grievance_workflow_steps')
                ->where('workflow_key', 'default')
                ->where('step_number', $step['step_number']);

            if ($workflowStep->exists()) {
                continue;
            }

            DB::table('grievance_workflow_steps')->insert([
                'workflow_key' => 'default',
                'step_number' => $step['step_number'],
                'name' => $step['name'],
                'description' => $step['description'],
                'action_type' => $step['action_type'],
                'role_name' => $step['role_name'],
                'approval_action' => $step['approval_action'],
                'rejection_action' => $step['rejection_action'],
                'rejection_target_step' => $step['rejection_target_step'] ?? null,
                'is_final_approval' => $step['is_final_approval'] ?? false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
