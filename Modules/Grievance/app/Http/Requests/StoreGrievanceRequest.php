<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievances.create') ?? false;
    }


    public function rules(): array
    {
        return [
            'grievance_category_id' => ['required', 'exists:grievance_categories,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'description' => ['required', 'string', 'max:2000'],
            'is_anonymous' => ['boolean'],
            'complainant_name' => ['nullable', 'string', 'max:150', 'required_if:is_anonymous,false'],
            'complainant_phone' => ['nullable', 'string', 'max:20', 'required_if:is_anonymous,false'],
            'complainant_email' => ['nullable', 'email', 'max:150'],
            'inbound_sms_id' => ['nullable', 'exists:inbound_sms,id'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf,mp4,mov,mp3,wav,m4a'],
        ];
    }
}
