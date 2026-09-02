<?php

return [
    'name' => 'Grievance',
    'public_sla_days' => (int) env('GRIEVANCE_PUBLIC_SLA_DAYS', 10),
    'recaptcha_secret' => env('RECAPTCHA_SECRET_KEY'),
];
