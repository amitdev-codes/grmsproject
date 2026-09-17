<?php

namespace Modules\Master\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGrievanceSlaPolicyRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('grievanceSlaPolicy') ?? $this->route('record') ?? $this->route('grievance-sla-policies');

        return [
            'code' => ['required', 'string', 'max:30', 'unique:grievance_sla_policies,code,' . $id],
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:10', 'in:low,medium,high,critical'],
            'acknowledgement_hours' => ['required', 'integer', 'min:1'],
            'resolution_hours' => ['required', 'integer', 'min:1'],
            'use_business_hours' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}