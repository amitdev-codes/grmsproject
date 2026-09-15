<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['section_manager', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}
