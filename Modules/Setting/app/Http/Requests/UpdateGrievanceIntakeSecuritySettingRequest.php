<?php

namespace Modules\Setting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Setting\Models\GrievanceIntakeSecuritySetting;

class UpdateGrievanceIntakeSecuritySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Super Admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        foreach (['whitelisted_ip_addresses', 'blacklisted_ip_addresses'] as $field) {
            $value = $this->input($field, '');

            if (! is_string($value)) {
                continue;
            }

            $entries = preg_split('/[\s,;]+/', trim((string) $value), flags: PREG_SPLIT_NO_EMPTY) ?: [];
            $this->merge([$field => array_values(array_unique($entries))]);
        }
    }

    public function rules(): array
    {
        return [
            'lodging_requests_per_minute' => ['required', 'integer', 'between:1,1000'],
            'duplicate_window_hours' => ['required', 'integer', 'between:0,8760'],
            'whitelisted_ip_addresses' => ['present', 'array', 'max:100'],
            'whitelisted_ip_addresses.*' => ['required', 'ip'],
            'blacklisted_ip_addresses' => ['present', 'array', 'max:100'],
            'blacklisted_ip_addresses.*' => ['required', 'ip'],
            'captcha_provider' => ['required', 'in:local,cloudflare_turnstile'],
            'cloudflare_site_key' => ['nullable', 'string', 'max:255', 'required_if:captcha_provider,cloudflare_turnstile'],
            'cloudflare_secret_key' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('captcha_provider') !== 'cloudflare_turnstile') {
                return;
            }

            if (filled($this->input('cloudflare_secret_key'))
                || filled(GrievanceIntakeSecuritySetting::current()->cloudflare_secret_key)) {
                return;
            }

            $validator->errors()->add(
                'cloudflare_secret_key',
                'Enter a Cloudflare secret key before enabling Turnstile.',
            );
        });
    }
}
