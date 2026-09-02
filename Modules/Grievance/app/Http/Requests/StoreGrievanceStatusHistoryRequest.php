<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGrievanceStatusHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageAuditLog') ?? false;
    }

    public function rules(): array
    {
        $statuses = [
            'submitted', 'acknowledged', 'allocated', 'in_progress',
            'delayed', 'resolved', 'closed', 'escalated', 'rejected',
        ];

        return [
            'grievance_id' => ['required', 'integer', 'exists:grievances,id'],
            'from_status' => ['nullable', Rule::in($statuses)],
            'to_status' => ['required', Rule::in($statuses)],
            'note' => ['nullable', 'string'],
        ];
    }
}
