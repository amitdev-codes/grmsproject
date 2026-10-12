<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Grievance\Models\Grievance;

class ResolveGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $grievance = $this->route('grievance');
        $step = $grievance instanceof Grievance ? $grievance->currentWorkflowStep : null;
        $user = $this->user();

        return in_array($step?->action_type, ['prepare_resolution', 'investigation'], true)
            && $step->is_active
            && $user
            && $grievance->assigned_officer_id === $user->id
            && ($step->approver_user_id
                ? $step->approver_user_id === $user->id
                : ($step->role_name && $user->hasRole($step->role_name)))
            && $user->can('grievances.resolve');
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:5000'],
            'resolution_html' => ['required', 'string', 'max:50000'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png'],
        ];
    }
}
