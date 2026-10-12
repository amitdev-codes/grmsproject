<?php

namespace Modules\Grievance\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Grievance\Services\Ai\AiTextGeneratorInterface;

class AnthropicDriver extends AbstractAiDriver implements AiTextGeneratorInterface
{
    public function generate(string $prompt): string
    {
        $key = config('services.anthropic.key');
        $model = config('services.anthropic.model');
        $this->assertConfigured($key, $model);

        $response = Http::withHeaders([
            'x-api-key' => $key,
            'anthropic-version' => '2023-06-01',
        ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => 500,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $this->assertSuccessful($response);

        return $this->requireText($response->json('content.0.text'));
    }
}
