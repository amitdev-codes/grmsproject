<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievances.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'grievance_category_id' => ['sometimes', 'required', 'exists:grievance_categories,id'],
            'channel_id' => ['sometimes', 'required', 'exists:grievance_channels,id'],
            'district_id' => ['sometimes', 'nullable', 'exists:districts,id'],
            'division_id' => ['sometimes', 'nullable', 'exists:divisions,id'],
            'section_id' => ['sometimes', 'nullable', 'exists:sections,id'],
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'remarks' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'is_anonymous' => ['sometimes', 'boolean'],
            'complainant_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'complainant_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'complainant_email' => ['sometimes', 'nullable', 'email', 'max:150'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf,mp4,mov,mp3,wav,m4a'],
            'remove_attachment_ids' => ['nullable', 'array'],
            'remove_attachment_ids.*' => ['integer'],
        ];
    }
}
