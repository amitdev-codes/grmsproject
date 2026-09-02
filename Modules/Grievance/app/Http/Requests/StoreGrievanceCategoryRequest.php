<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGrievanceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_st' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:grievance_categories,slug'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_sensitive' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
