<?php

// Cloudflare's documented test credentials work only in local/test environments.
// Deployed environments must provide their own keys.
$turnstileTestKey = in_array(env('APP_ENV'), ['local', 'testing'], true)
    ? '1x00000000000000000000AA'
    : null;
$turnstileTestSecret = in_array(env('APP_ENV'), ['local', 'testing'], true)
    ? '1x0000000000000000000000000000000AA'
    : null;

return [
    'name' => 'Grievance',
    'public_sla_days' => (int) env('GRIEVANCE_PUBLIC_SLA_DAYS', 10),
    // Cloudflare Turnstile is free and privacy-friendly. Server-side verification
    // is mandatory. Local development uses Cloudflare's official test pair.
    'turnstile_site_key' => env('TURNSTILE_SITE_KEY', $turnstileTestKey),
    'turnstile_secret_key' => env('TURNSTILE_SECRET_KEY', $turnstileTestSecret),
    'turnstile_action' => 'grievance_submission',
    'turnstile_required' => (bool) env('TURNSTILE_REQUIRED', true),
];
