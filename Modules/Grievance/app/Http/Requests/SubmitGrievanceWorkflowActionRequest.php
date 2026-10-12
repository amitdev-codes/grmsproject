<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Grievance\Models\Grievance;

class SubmitGrievanceWorkflowActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $step = $this->grievance()?->currentWorkflowStep;
        $user = $this->user();

        if (! $step || ! $user || ! $step->is_active) {
            return false;
        }

        $isApprover = $step->approver_user_id
            ? $step->approver_user_id === $user->id
            : ($step->role_name && $user->hasRole($step->role_name));

        if (! $isApprover
            || (($step->action_type === 'review' && $step->role_name === 'Helpdesk Officer'
                || in_array($step->action_type, ['prepare_resolution', 'investigation', 'acknowledge'], true))
                && $this->grievance()?->assigned_officer_id !== $user->id)) {
            return false;
        }

        $permission = match ($step->action_type) {
            'allocate_division', 'allocate_section', 'screen_allocate' => 'grievances.allocate',
            'assign_officer' => 'grievances.assign',
            'prepare_resolution', 'investigation', 'final_approval', 'acknowledge', 'decision_approval' => 'grievances.resolve',
            'closure' => 'grievances.close',
            'escalation' => 'grievances.escalate',
            default => 'grievances.view',
        };

        return $user->can($permission);
    }

    public function rules(): array
    {
        /** @var Grievance $grievance */
        $grievance = $this->route('grievance');
        $step = $grievance->currentWorkflowStep;

        return [
            'decision' => ['required', Rule::in(['approve', 'reject', 'acknowledge'])],
            'reason' => ['nullable', 'string', 'max:5000', 'required_if:decision,reject'],
            'division_id' => [
                Rule::requiredIf(fn (): bool => $this->input('decision') === 'approve' && in_array($step?->action_type, ['allocate_division', 'screen_allocate'], true)),
                'nullable',
                'integer',
                'exists:divisions,id',
            ],
            'section_id' => [
                Rule::requiredIf(fn (): bool => $this->input('decision') === 'approve' && in_array($step?->action_type, ['allocate_section', 'screen_allocate'], true)),
                'nullable',
                'integer',
                'exists:sections,id',
            ],
            'officer_id' => [
                Rule::requiredIf(fn (): bool => $this->input('decision') === 'approve' && in_array($step?->action_type, ['assign_officer', 'screen_allocate'], true)),
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'resolution_html' => [
                Rule::requiredIf(fn (): bool => $this->input('decision') === 'approve' && in_array($step?->action_type, ['prepare_resolution', 'investigation'], true)),
                'nullable',
                'string',
                'max:50000',
            ],
            'workflow_data' => ['nullable', 'array'],
            'workflow_data.informal_outcome' => ['nullable', 'string', 'max:5000'],
            'workflow_data.severity' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'workflow_data.repeat_case' => ['nullable', 'boolean'],
            'workflow_data.requested_remedy' => ['nullable', 'string', 'max:5000'],
            'workflow_data.corrective_action' => ['nullable', 'string', 'max:5000'],
            'workflow_data.action_owner' => ['nullable', 'string', 'max:255'],
            'workflow_data.action_due_at' => ['nullable', 'date'],
            'workflow_data.acceptance' => ['nullable', 'boolean'],
            'workflow_data.satisfaction_rating' => ['nullable', 'integer', 'between:1,5'],
            'workflow_data.referral_authority' => ['nullable', 'string', 'max:255'],
            'workflow_data.referral_date' => ['nullable', 'date'],
            'workflow_data.external_review_requested' => ['nullable', 'boolean'],
            'workflow_data.monitoring_notes' => ['nullable', 'string', 'max:5000'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png'],
        ];
    }

    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $validator): void {
            $step = $this->grievance()?->currentWorkflowStep;

            if ($this->input('decision') !== 'approve') {
                return;
            }

            $requiredFields = match ($step?->action_type) {
                'informal_resolution' => ['workflow_data.informal_outcome'],
                'closure' => ['workflow_data.acceptance', 'workflow_data.satisfaction_rating'],
                'escalation' => ['workflow_data.referral_authority', 'workflow_data.referral_date'],
                'monitoring' => ['workflow_data.monitoring_notes'],
                default => [],
            };

            foreach ($requiredFields as $field) {
                if (! $this->filled($field)) {
                    $validator->errors()->add($field, 'This field is required for this workflow action.');
                }
            }
        });
    }

    private function grievance(): ?Grievance
    {
        $grievance = $this->route('grievance');

        return $grievance instanceof Grievance ? $grievance : null;
    }
}
