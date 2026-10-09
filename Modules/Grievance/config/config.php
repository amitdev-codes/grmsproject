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
    'ai' => [
        'enabled' => (bool) env('GRIEVANCE_AI_ENABLED', false),
        'provider' => env('GRIEVANCE_AI_PROVIDER', 'anthropic'),
        'confidence_threshold' => (float) env('GRIEVANCE_AI_CONFIDENCE_THRESHOLD', 0.70),
    ],
    'speech_to_text' => [
        'enabled' => (bool) env('GRIEVANCE_SPEECH_TO_TEXT_ENABLED', true),
        'provider' => env('GRIEVANCE_SPEECH_TO_TEXT_PROVIDER', 'browser'),
        'language' => env('GRIEVANCE_SPEECH_TO_TEXT_LANGUAGE', 'en-ZA'),
    ],
    'ussd' => [
        'enabled' => (bool) env('GRIEVANCE_USSD_ENABLED', true),
        'service_code' => env('GRIEVANCE_USSD_SERVICE_CODE'),
        'shared_secret' => env('GRIEVANCE_USSD_SHARED_SECRET'),
        'max_description_length' => (int) env('GRIEVANCE_USSD_MAX_DESCRIPTION_LENGTH', 500),
    ],
    'notifications' => [
        'enabled' => (bool) env('GRIEVANCE_NOTIFICATIONS_ENABLED', true),
        'statuses' => array_fill_keys(
            array_filter(array_map('trim', explode(',', env(
                'GRIEVANCE_NOTIFICATION_STATUSES',
                'submitted,allocated_division,allocated_section,assigned_officer,reallocation_required,in_progress,resolved,closed,rejected',
            )))),
            true,
        ),
    ],
    // Cloudflare Turnstile is free and privacy-friendly. Server-side verification
    // is mandatory. Local development uses Cloudflare's official test pair.
    'turnstile_site_key' => env('TURNSTILE_SITE_KEY', $turnstileTestKey),
    'turnstile_secret_key' => env('TURNSTILE_SECRET_KEY', $turnstileTestSecret),
    'turnstile_action' => 'grievance_submission',
    'turnstile_required' => (bool) env('TURNSTILE_REQUIRED', true),
];
