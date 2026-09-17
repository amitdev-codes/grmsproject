<?php

namespace Modules\Master\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectTypeRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('projectType') ?? $this->route('record') ?? $this->route('project-type');

        return [
            'code' => ['required', 'string', 'max:30', 'unique:project_types,code,' . $id],
            'name' => ['required', 'string', 'max:255'],
            'name_st' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}