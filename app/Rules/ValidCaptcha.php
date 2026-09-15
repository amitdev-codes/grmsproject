<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Session;

class ValidCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (! is_string($value) || $value === '' || strlen($value) > 20) {
            $fail('Please complete the security verification.');

            return;
        }

        $expected = Session::get('grievance_captcha_answer');

        if (! $expected || ! hash_equals((string) $expected, trim($value))) {
            $fail('Captcha verification failed. Please try again.');

            return;
        }

        Session::forget('grievance_captcha_answer');
    }
}
