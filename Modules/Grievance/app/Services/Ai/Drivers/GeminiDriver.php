<?php

namespace Modules\Grievance\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Grievance\Services\Ai\AiTextGeneratorInterface;

class GeminiDriver extends AbstractAiDriver implements AiTextGeneratorInterface
{
    public function generate(string $prompt): string
    {
        $model = config('services.gemini.model');
        $key = config('services.gemini.key');
        $this->assertConfigured($key, $model);

        $response = Http::timeout(15)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}",
            [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]
        );

        $this->assertSuccessful($response);

        return $this->requireText($response->json('candidates.0.content.parts.0.text'));
    }
}
