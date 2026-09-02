<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGrievanceEscalationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'grievance_id' => ['sometimes', 'required', 'integer', 'exists:grievances,id'],
            'escalation_level' => ['sometimes', 'required', 'integer', 'between:1,3'],
            'escalated_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'resolved' => ['sometimes', 'boolean'],
            'reason' => ['sometimes', 'nullable', 'string'],
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
