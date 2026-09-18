<?php

namespace Modules\Grievance\Services\Ai\Drivers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\Grievance\Services\Ai\AiClassifierDriverInterface;

class AnthropicDriver extends AbstractAiDriver implements AiClassifierDriverInterface
{
    /**
     * @throws ConnectionException
     */
    public function classify(string $description, array $categories): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 300,
            'messages' => [
                ['role' => 'user', 'content' => $this->buildPrompt($description, $categories)],
            ],
        ]);

        if ($response->failed()) {
            return ['category_id' => null, 'confidence' => 0.0, 'reason' => 'api_error'];
        }

        return $this->parseJson($response->json('content.0.text'));
    }
}
