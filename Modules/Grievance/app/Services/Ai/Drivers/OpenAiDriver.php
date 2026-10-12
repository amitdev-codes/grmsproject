<?php

namespace Modules\Grievance\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Grievance\Services\Ai\AiTextGeneratorInterface;

class OpenAiDriver extends AbstractAiDriver implements AiTextGeneratorInterface
{
    public function generate(string $prompt): string
    {
        $key = config('services.openai.key');
        $model = config('services.openai.model');
        $this->assertConfigured($key, $model);

        $response = Http::withToken($key)
            ->timeout(15)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ]);

        $this->assertSuccessful($response);

        return $this->requireText($response->json('choices.0.message.content'));
    }
}
