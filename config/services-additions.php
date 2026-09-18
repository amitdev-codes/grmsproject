<?php

// Merge these entries into the existing array returned by config/services.php
// (do not overwrite the file — just add these keys).

return [
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    'grok' => [
        'key' => env('GROK_API_KEY'),
        'model' => env('GROK_MODEL', 'grok-2-latest'),
    ],
];
