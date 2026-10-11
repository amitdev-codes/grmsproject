<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResolutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $resolution = $this->route('resolution');

        // Once approved, a resolution shouldn't be silently rewritten —
        // only unapproved (proposed) resolutions are freely editable here.
        if ($resolution->approved_at !== null) {
            return $this->user()?->can('manageApprovedResolution', $resolution) ?? false;
        }

        return $this->user()?->can('update', $resolution) ?? false;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['sometimes', 'required', 'in:note,memo,report'],
            'resolution_text' => ['sometimes', 'required', 'string'],

            'complainant_confirmed_at' => ['sometimes', 'nullable', 'date'],

            'rejected_reason' => [
                Rule::requiredIf(fn () => $this->boolean('rejected')),
                'nullable', 'string',
            ],
        ];
    }
}
