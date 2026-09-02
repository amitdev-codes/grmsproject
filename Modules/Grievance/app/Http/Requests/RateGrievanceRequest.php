<?php

namespace Modules\Grievance\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class RateGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'satisfaction_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'contact' => ['nullable', 'string'],
        ];
    }
}
