<?php

namespace Modules\Grievance\Http\Requests;

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
            || ($step->action_type === 'prepare_resolution'
                && $this->grievance()?->assigned_officer_id !== $user->id)) {
            return false;
        }

        $permission = match ($step->action_type) {
            'allocate_division', 'allocate_section' => 'grievances.allocate',
            'assign_officer' => 'grievances.assign',
            'prepare_resolution', 'final_approval' => 'grievances.resolve',
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
                Rule::requiredIf(fn (): bool => $this->input('decision') === 'approve' && $step?->action_type === 'allocate_division'),
                'nullable',
                'integer',
                'exists:divisions,id',
            ],
            'section_id' => [
                Rule::requiredIf(fn (): bool => $this->input('decision') === 'approve' && $step?->action_type === 'allocate_section'),
                'nullable',
                'integer',
                'exists:sections,id',
            ],
            'officer_id' => [
                Rule::requiredIf(fn (): bool => $this->input('decision') === 'approve' && $step?->action_type === 'assign_officer'),
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'resolution_html' => [
                Rule::requiredIf(fn (): bool => $this->input('decision') === 'approve' && $step?->action_type === 'prepare_resolution'),
                'nullable',
                'string',
                'max:50000',
            ],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png'],
        ];
    }

    private function grievance(): ?Grievance
    {
        $grievance = $this->route('grievance');

        return $grievance instanceof Grievance ? $grievance : null;
    }
}
