<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('grievance_categories', 'id')->where('is_active', true),
            ],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],

            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'location_description' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            'metadata' => ['nullable', 'array'],
            'metadata.*' => ['nullable', 'string', 'max:255'],

            'is_anonymous' => ['required', 'boolean'],
            'contact_name' => ['nullable', 'required_if:is_anonymous,0', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],

            'submitted_via' => ['nullable', Rule::in(['web', 'officer_assisted', 'sms', 'whatsapp', 'ussd'])],

            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,heic', 'max:8192'], // 8MB, matches ACCEPTED_MIME_TYPES + MAX_ATTACHMENT_MB
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_anonymous' => filter_var($this->input('is_anonymous', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Cross-field rule the base `rules()` array can't express on its own:
     * a named complainant must leave a phone number or an email (either is fine).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->boolean('is_anonymous')
                && ! $this->filled('contact_phone')
                && ! $this->filled('contact_email')
            ) {
                $validator->errors()->add(
                    'contact_phone',
                    'Provide a phone number or an email so an officer can reach you.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'description.min' => 'Please describe what happened in a bit more detail (at least 20 characters).',
            'attachments.*.mimes' => 'Photos must be JPG, PNG, WEBP or HEIC.',
            'attachments.*.max' => 'Each photo must be 8MB or smaller.',
        ];
    }
}
