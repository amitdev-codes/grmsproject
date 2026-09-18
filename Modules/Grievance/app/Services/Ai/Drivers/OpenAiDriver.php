<?php

namespace Modules\Grievance\Services\Ai\Drivers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\Grievance\Services\Ai\AiClassifierDriverInterface;

class OpenAiDriver extends AbstractAiDriver implements AiClassifierDriverInterface
{
    /**
     * @throws ConnectionException
     */
    public function classify(string $description, array $categories): array
    {
        $response = Http::withToken(config('services.openai.key'))
            ->timeout(15)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model'),
                'messages' => [
                    ['role' => 'user', 'content' => $this->buildPrompt($description, $categories)],
                ],
                'temperature' => 0,
            ]);

        if ($response->failed()) {
            return ['category_id' => null, 'confidence' => 0.0, 'reason' => 'api_error'];
        }

        return $this->parseJson($response->json('choices.0.message.content'));
    }
}
