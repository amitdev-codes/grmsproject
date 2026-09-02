<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignOfficerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievance.assign_officer', $this->route('grievance')) ?? false;
    }

    public function rules(): array
    {
        return [
            'officer_id' => ['required', 'exists:users,id'],
        ];
    }
}
