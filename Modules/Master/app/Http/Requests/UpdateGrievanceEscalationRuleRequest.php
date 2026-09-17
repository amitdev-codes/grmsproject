<?php

namespace Modules\Master\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGrievanceEscalationRuleRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('grievanceEscalationRule') ?? $this->route('record') ?? $this->route('grievance-escalation-rules');

        return [
            'grievance_sla_policy_id' => ['nullable', 'integer', 'exists:grievance_sla_policies,id'],
            'escalation_level' => ['required', 'integer', 'min:1', 'max:255'],
            'breach_after_hours' => ['required', 'integer', 'min:1'],
            'extension_hours' => ['nullable', 'integer', 'min:1'],
            'target_role' => ['required', 'string', 'max:50', 'in:zonal_officer,regional_head,director_roads'],
            'requires_manual_review' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}