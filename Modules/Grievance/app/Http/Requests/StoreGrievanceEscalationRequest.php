<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGrievanceEscalationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'grievance_id' => ['required', 'integer', 'exists:grievances,id'],
            'escalation_level' => ['required', 'integer', 'between:1,3'],
            'escalated_to' => ['nullable', 'integer', 'exists:users,id'],
//            'sla_breached_at' => ['required', 'date'], // see note below
            'reason' => ['nullable', 'string'],
            'resolved' => ['boolean'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
