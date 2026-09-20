<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocateDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Director') ?? false;
    }

    public function rules(): array
    {
        return [
            'division_id' => ['required', 'exists:divisions,id'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
