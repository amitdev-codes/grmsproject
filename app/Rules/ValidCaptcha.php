<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\Grievance\Services\GrievanceIntakeSecurityService;

class ValidCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (! is_string($value) || $value === '' || strlen($value) > 2048) {
            $fail('Please complete the security verification.');

            return;
        }

        if (! app(GrievanceIntakeSecurityService::class)->verifyWebCaptcha(
            $value,
            (string) request()->ip(),
        )) {
            $fail('Captcha verification failed. Please try again.');
        }
    }
}
