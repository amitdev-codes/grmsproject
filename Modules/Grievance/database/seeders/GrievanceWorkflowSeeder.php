<?php

namespace Modules\Grievance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Grievance\Models\GrievanceWorkflowStep;

class GrievanceWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            [
                'step_number' => 1,
                'name' => 'Intake and informal resolution',
                'description' => 'Record the initial contact and attempt an informal resolution. If unresolved, forward for formal submission.',
                'action_type' => 'informal_resolution',
                'role_name' => 'RD Officer',
                'approval_action' => 'advance',
                'approval_target_step' => 9,
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 2,
            ],
            [
                'step_number' => 2,
                'name' => 'Formal submission',
                'description' => 'Register the formal grievance, reference, incident, location, requested remedy and available evidence.',
                'action_type' => 'review',
                'role_name' => 'Helpdesk Officer',
                'approval_action' => 'advance',
                'rejection_action' => 'reject',
            ],
            [
                'step_number' => 3,
                'name' => 'Registration, screening and allocation',
                'description' => 'Confirm category and severity, screen repeat cases and allocate the division, section and investigator.',
                'action_type' => 'screen_allocate',
                'role_name' => 'Responsible Manager',
                'approval_action' => 'advance',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 2,
            ],
            [
                'step_number' => 4,
                'name' => 'Acknowledgement',
                'description' => 'The investigator acknowledges receipt, requests missing information if needed and confirms the expected resolution date.',
                'action_type' => 'acknowledge',
                'role_name' => 'Investigator',
                'approval_action' => 'advance',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 3,
            ],
            [
                'step_number' => 5,
                'name' => 'Investigation and resolution proposal',
                'description' => 'The assigned Investigator records findings and evidence, prepares the resolution draft and corrective measures, then submits the proposed decision to the GRC Chair for approval.',
                'action_type' => 'investigation',
                'role_name' => 'Investigator',
                'approval_action' => 'advance',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 3,
            ],
            [
                'step_number' => 6,
                'name' => 'Decision and approval',
                'description' => 'Review the preliminary finding, approve and communicate the decision, or route an unresolved case for escalation.',
                'action_type' => 'decision_approval',
                'role_name' => 'GRC Chair',
                'approval_action' => 'advance',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 8,
            ],
            [
                'step_number' => 7,
                'name' => 'Notification, closure and satisfaction',
                'description' => 'Notify the complainant, verify corrective action and record acceptance and satisfaction. Unaccepted outcomes may return to investigation.',
                'action_type' => 'closure',
                'role_name' => 'Contractor CLO',
                'approval_action' => 'advance',
                'approval_target_step' => 9,
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 5,
            ],
            [
                'step_number' => 8,
                'name' => 'Escalation and external review',
                'description' => 'Refer unresolved cases to the RD Environment and Social Affairs Unit or the applicable external review body.',
                'action_type' => 'escalation',
                'role_name' => 'RD E&S Unit',
                'approval_action' => 'advance',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 5,
            ],
            [
                'step_number' => 9,
                'name' => 'Performance monitoring and reporting',
                'description' => 'Record the case outcome for monthly tracking and annual or executive reporting, including risks and overdue actions.',
                'action_type' => 'monitoring',
                'role_name' => 'Executive',
                'approval_action' => 'advance',
                'rejection_action' => 'return_to_step',
                'rejection_target_step' => 5,
            ],
        ];

        foreach ($steps as $step) {
            GrievanceWorkflowStep::query()->updateOrCreate(
                [
                    'workflow_key' => 'default',
                    'step_number' => $step['step_number'],
                ],
                [
                    ...$step,
                    'is_final_approval' => false,
                    'is_active' => true,
                ],
            );
        }
    }
}
