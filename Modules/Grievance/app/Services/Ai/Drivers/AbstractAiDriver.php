<?php

namespace Modules\Grievance\Services\Ai\Drivers;

use Illuminate\Http\Client\Response;
use Modules\Grievance\Services\Ai\AiSummaryException;

abstract class AbstractAiDriver
{
    protected function assertConfigured(?string $key, ?string $model): void
    {
        if (! $key || ! $model) {
            throw new AiSummaryException('The configured AI provider is missing its API key or model setting.');
        }
    }

    protected function assertSuccessful(Response $response): void
    {
        if ($response->failed()) {
            throw new AiSummaryException("The configured AI provider returned HTTP {$response->status()}.");
        }
    }

    protected function requireText(?string $text): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            throw new AiSummaryException('The configured AI provider returned an empty summary.');
        }

        return $text;
    }
}
