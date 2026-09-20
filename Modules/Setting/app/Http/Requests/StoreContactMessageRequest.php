<?php

namespace Modules\Setting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_anonymous' => ['nullable', 'boolean'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please provide an email address.',
            'email.email' => 'Please provide a valid email address.',
            'message.required' => 'Please enter a message.',
            'message.min' => 'Please provide at least 10 characters.',
            'message.max' => 'Please keep the message under 5,000 characters.',
            'website.max' => 'Submission rejected.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $fields = ['name', 'email', 'mobile', 'subject', 'message', 'website'];
        $clean = [];

        foreach ($fields as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $clean[$field] = trim(strip_tags($value));
            }
        }

        $this->merge($clean);
    }
}
