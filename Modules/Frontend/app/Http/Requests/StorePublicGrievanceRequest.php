<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicGrievanceRequest extends FormRequest
{
    protected const MAX_ATTACHMENTS = 5;
    protected const MAX_ATTACHMENT_KB = 8 * 1024;
    protected const ACCEPTED_MIMES = ['jpeg', 'png', 'webp', 'heic'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:grievance_categories,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],

            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'location_description' => ['nullable', 'string', 'max:255'],

            'is_anonymous' => ['required', Rule::in(['0', '1', 0, 1, true, false])],

            'contact_name' => ['nullable', 'required_if:is_anonymous,0', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'required_without_if:is_anonymous,0,contact_email', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],

            'submitted_via' => ['nullable', 'string', 'in:web,phone,walk_in,email'],

            'metadata' => ['nullable', 'array'],
            'metadata.*' => ['nullable', 'string', 'max:500'],

            'attachments' => ['nullable', 'array', 'max:'.self::MAX_ATTACHMENTS],
            'attachments.*' => [
                'file',
                'max:'.self::MAX_ATTACHMENT_KB,
                'mimes:'.implode(',', self::ACCEPTED_MIMES),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'description.min' => 'Please provide at least 20 characters describing what happened.',
            'attachments.max' => 'You can attach up to '.self::MAX_ATTACHMENTS.' photos.',
            'attachments.*.max' => 'Each photo must be '.(self::MAX_ATTACHMENT_KB / 1024).'MB or smaller.',
            'attachments.*.mimes' => 'Photos must be JPG, PNG, WEBP, or HEIC.',
            'contact_phone.required_without_if' => 'Please provide a phone number or an email so an officer can reach you.',
            'contact_name.required_if' => 'Please provide your name, or file anonymously instead.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_anonymous' => filter_var($this->input('is_anonymous'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
        ]);
    }
}
