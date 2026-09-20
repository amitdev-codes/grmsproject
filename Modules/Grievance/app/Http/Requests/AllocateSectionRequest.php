<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Division Director') ?? false;
    }

    public function rules(): array
    {
        return [
            'section_id' => ['required', 'exists:sections,id'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
