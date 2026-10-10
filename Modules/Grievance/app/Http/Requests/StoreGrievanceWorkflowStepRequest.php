<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGrievanceWorkflowStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievance_workflow_steps.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'step_number' => ['required', 'integer', 'min:1', Rule::unique('grievance_workflow_steps')->where('workflow_key', 'default')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'role_name' => ['nullable', 'string', 'max:255', 'required_without:approver_user_id', 'exists:roles,name,guard_name,web'],
            'approver_user_id' => ['nullable', 'integer', 'exists:users,id', 'required_without:role_name'],
            'approval_action' => ['required', Rule::in(['advance', 'resolve'])],
            'rejection_action' => ['required', Rule::in(['reject', 'return_to_step'])],
            'rejection_target_step' => ['nullable', 'integer', 'min:1', 'lt:step_number', 'required_if:rejection_action,return_to_step', Rule::exists('grievance_workflow_steps', 'step_number')->where('workflow_key', 'default')],
            'is_final_approval' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
