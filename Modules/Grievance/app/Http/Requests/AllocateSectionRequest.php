<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievance.allocate_section', $this->route('grievance')) ?? false;
    }

    public function rules(): array
    {
        return [
            'section_id' => ['required', 'exists:sections,id'],
        ];
    }
}
