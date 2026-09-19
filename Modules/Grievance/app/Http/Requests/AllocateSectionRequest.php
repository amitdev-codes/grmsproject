<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Division Director', 'Super Admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'section_id' => ['required', 'exists:sections,id'],
        ];
    }
}
