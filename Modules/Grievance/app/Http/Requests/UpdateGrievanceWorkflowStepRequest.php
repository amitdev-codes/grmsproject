<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Grievance\Models\GrievanceWorkflowStep;

class UpdateGrievanceWorkflowStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievance_workflow_steps.edit') ?? false;
    }

    public function rules(): array
    {
        /** @var GrievanceWorkflowStep $step */
        $step = $this->route('grievance_workflow_step');

        return [
            'step_number' => ['required', 'integer', 'min:1', Rule::unique('grievance_workflow_steps')->where('workflow_key', 'default')->ignore($step->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'action_type' => ['required', Rule::in([
                'review', 'allocate_division', 'allocate_section', 'assign_officer',
                'prepare_resolution', 'final_approval', 'informal_resolution',
                'screen_allocate', 'acknowledge', 'decision_approval', 'closure',
                'investigation',
                'escalation', 'monitoring',
            ])],
            'role_name' => ['nullable', 'string', 'max:255', 'required_without:approver_user_id', 'exists:roles,name,guard_name,web'],
            'approver_user_id' => ['nullable', 'integer', 'exists:users,id', 'required_without:role_name'],
            'approval_action' => ['required', Rule::in(['advance', 'resolve'])],
            'approval_target_step' => ['nullable', 'integer', 'min:1', Rule::exists('grievance_workflow_steps', 'step_number')->where('workflow_key', 'default')],
            'rejection_action' => ['required', Rule::in(['reject', 'return_to_step'])],
            'rejection_target_step' => ['nullable', 'integer', 'min:1', 'required_if:rejection_action,return_to_step', Rule::exists('grievance_workflow_steps', 'step_number')->where('workflow_key', 'default')],
            'is_final_approval' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $validator): void {
            $isFinal = $this->boolean('is_final_approval');
            $isFinalAction = $this->input('action_type') === 'final_approval';
            $resolves = $this->input('approval_action') === 'resolve';

            if ($isFinal !== $isFinalAction || $isFinal !== $resolves) {
                $validator->errors()->add(
                    'action_type',
                    'Final approval steps must use the final approval action and resolve the grievance.',
                );
            }

            foreach (['approval_target_step', 'rejection_target_step'] as $targetField) {
                if ($this->filled($targetField)
                    && (int) $this->input($targetField) === (int) $this->input('step_number')) {
                    $validator->errors()->add($targetField, 'A workflow step cannot target itself.');
                }
            }
        });
    }
}
