<?php

namespace Modules\Master\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code'         => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections', 'code')->ignore($this->route('section')),
            ],
            'division_id'  => ['required', 'exists:divisions,id'],
            'name'         => ['required', 'string', 'max:255'],
            'name_st'      => ['nullable', 'string', 'max:255'],
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
