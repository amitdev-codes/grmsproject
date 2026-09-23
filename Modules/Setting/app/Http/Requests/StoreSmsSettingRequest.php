<?php

namespace Modules\Setting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSmsSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Super Admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'provider' => ['required', 'string', 'max:100'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:1000'],
            'api_secret' => ['nullable', 'string', 'max:1000'],
            'sender_id' => ['nullable', 'string', 'max:50'],
            'default_country_code' => ['required', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ];
    }
}
