<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
class StorePublicGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:grievance_categories,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            'location_description' => ['nullable', 'string', 'max:255'],
            'is_anonymous' => ['required', 'boolean'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'metadata' => ['nullable', 'array'],
            'metadata.*' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:8192', 'mimes:jpg,jpeg,png,webp,heic'],
            'captcha_token' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (!$this->boolean('is_anonymous')
                && !$this->filled('contact_phone')
                && !$this->filled('contact_email')) {
                $v->errors()->add('contact_phone', 'Provide a phone number or an email so an officer can reach you.');
            }
        });
    }
}
