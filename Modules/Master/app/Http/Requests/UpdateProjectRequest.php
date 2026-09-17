<?php

namespace Modules\Master\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('project') ?? $this->route('record') ?? $this->route('projects');

        return [
            'code' => ['required', 'string', 'max:40', 'unique:projects,code,' . $id],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_type_id' => ['nullable', 'integer', 'exists:project_types,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'consultant_id' => ['nullable', 'integer', 'exists:service_providers,id'],
            'contractor_id' => ['nullable', 'integer', 'exists:service_providers,id'],
            'contract_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3', 'default:LSL'],
            'starts_on' => ['nullable', 'date'],
            'expected_completion_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'completed_on' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:planned,current,completed,archived'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}