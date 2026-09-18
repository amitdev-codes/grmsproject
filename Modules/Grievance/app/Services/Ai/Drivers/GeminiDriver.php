<?php

namespace Modules\Grievance\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Grievance\Services\Ai\AiClassifierDriverInterface;

class GeminiDriver extends AbstractAiDriver implements AiClassifierDriverInterface
{
    public function classify(string $description, array $categories): array
    {
        $model = config('services.gemini.model');
        $key = config('services.gemini.key');

        $response = Http::timeout(15)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}",
            [
                'contents' => [
                    ['parts' => [['text' => $this->buildPrompt($description, $categories)]]],
                ],
            ]
        );

        if ($response->failed()) {
            return ['category_id' => null, 'confidence' => 0.0, 'reason' => 'api_error'];
        }

        return $this->parseJson($response->json('candidates.0.content.parts.0.text'));
    }
}
