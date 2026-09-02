<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGrievanceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $grievanceCategory = $this->route('grievance_category') ?? $this->route('grievanceCategory');

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_st' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grievance_categories', 'slug')->ignore($grievanceCategory?->id),
            ],
            'division_id' => ['nullable', 'integer'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_sensitive' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
