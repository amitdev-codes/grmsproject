<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartInvestigationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Helpdesk Officer', 'Content Editor'])
            && $this->route('grievance')?->assigned_officer_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
