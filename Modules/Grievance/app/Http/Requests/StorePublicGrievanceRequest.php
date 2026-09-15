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
            'project_id' => ['nullable', 'exists:projects,id'],
            'source_grievance_reference' => ['nullable', 'string', 'max:20', 'exists:grievances,reference_no'],
            'is_previously_lodged' => ['sometimes', 'boolean'],
            'is_previously_finalized' => ['sometimes', 'boolean'],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            'location_description' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'location_accuracy_meters' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'preferred_language' => ['nullable', 'in:en,st'],
            'is_anonymous' => ['required', 'boolean'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'metadata' => ['nullable', 'array'],
            'metadata.*' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,heic,pdf,doc,docx,xls,xlsx,mp3,wav,m4a,mp4,mov,avi'],
            'captcha_token' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (! $this->boolean('is_anonymous')
                && ! $this->filled('contact_phone')
                && ! $this->filled('contact_email')) {
                $v->errors()->add('contact_phone', 'Provide a phone number or an email so an officer can reach you.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_anonymous' => $this->boolean('is_anonymous'),
            'is_previously_lodged' => $this->boolean('is_previously_lodged'),
            'is_previously_finalized' => $this->boolean('is_previously_finalized'),
        ]);
    }
}
