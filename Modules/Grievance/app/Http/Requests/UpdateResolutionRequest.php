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
        $resolution = $this->route('resolution');
        $alreadyApproved = $resolution?->approved_at !== null;

        return [
            'resolution_text' => ['sometimes', 'required', 'string'],

            // Approval fields — only meaningful once, guarded by authorize()
            // above; kept nullable/sometimes so a plain text edit doesn't
            // need to resend them.
            'approved_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'approved_at' => ['sometimes', 'nullable', 'date'],

            'complainant_confirmed_at' => ['sometimes', 'nullable', 'date'],

            'rejected_reason' => [
                Rule::requiredIf(fn () => $this->boolean('rejected')),
                'nullable', 'string',
            ],
        ];
    }
}
