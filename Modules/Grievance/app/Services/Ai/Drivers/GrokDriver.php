<?php

namespace Modules\Grievance\Services\Ai\Drivers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\Grievance\Services\Ai\AiClassifierDriverInterface;

class GrokDriver extends AbstractAiDriver implements AiClassifierDriverInterface
{
    /**
     * @throws ConnectionException
     */
    public function classify(string $description, array $categories): array
    {
        $response = Http::withToken(config('services.grok.key'))
            ->timeout(15)
            ->post('https://api.x.ai/v1/chat/completions', [
                'model' => config('services.grok.model'),
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
