<?php

namespace Modules\Master\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code'         => ['required', 'string', 'max:255', 'unique:sections,code'],
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
