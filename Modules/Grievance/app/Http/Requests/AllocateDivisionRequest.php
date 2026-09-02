<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocateDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievance.allocate_division') ?? false;
    }

    public function rules(): array
    {
        return [
            'division_id' => ['required', 'exists:divisions,id'],
        ];
    }
}
