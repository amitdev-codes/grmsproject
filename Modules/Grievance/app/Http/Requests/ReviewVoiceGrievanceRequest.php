<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewVoiceGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievances.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'transcript_original' => ['required', 'string', 'min:5', 'max:5000'],
            'transcript_english' => ['required', 'string', 'min:20', 'max:5000'],
            'language_detected' => ['nullable', 'in:en,st,zu,xh,other'],
            'category_id' => $this->routeIs('voice-grievances.file')
                ? ['required', 'integer', Rule::exists('grievance_categories', 'id')->where('is_active', true)]
                : ['nullable', 'integer', 'exists:grievance_categories,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
        ];
    }
}
