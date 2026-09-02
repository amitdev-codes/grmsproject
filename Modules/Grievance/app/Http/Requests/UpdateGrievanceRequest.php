<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
//        return $this->user()?->can('update', $this->route('grievance')) ?? false;
        return true;
    }

    public function rules(): array
    {
        return [
            'grievance_category_id' => ['sometimes', 'required', 'exists:grievance_categories,id'],
            'district_id' => ['sometimes', 'nullable', 'exists:districts,id'],
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'is_anonymous' => ['sometimes', 'boolean'],
            'complainant_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'complainant_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'complainant_email' => ['sometimes', 'nullable', 'email', 'max:150'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf,mp4,mov,mp3,wav,m4a'],
            'remove_media_ids' => ['nullable', 'array'],
            'remove_media_ids.*' => ['integer'],
        ];
    }
}
