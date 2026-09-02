<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ValidCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $secret = config('grievances.recaptcha_secret');

        if (!$secret) {
            return; // not configured yet — no-op in local/dev; require before go-live
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $value,
        ]);

        if (!$response->ok() || !($response->json('success') ?? false)) {
            $fail('Captcha verification failed. Please try again.');
        }
    }
}
