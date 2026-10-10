<?php

namespace Modules\Setting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Super Admin', 'Admin', 'IT Admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'translation_key' => ['required', 'string', 'max:191', 'regex:/^[^\r\n]+$/u', Rule::unique('application_translations')],
            'english_text' => ['required', 'string', 'max:10000'],
            'sesotho_text' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
