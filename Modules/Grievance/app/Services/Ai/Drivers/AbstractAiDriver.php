<?php

namespace Modules\Grievance\Services\Ai\Drivers;

abstract class AbstractAiDriver
{
    protected function buildPrompt(string $description, array $categories): string
    {
        $list = collect($categories)
            ->map(fn ($c) => "{$c['id']}: {$c['name']}")
            ->implode("\n");

        return <<<PROMPT
        You are a grievance triage classifier for a Rural Development GRMS.
        Pick the single best matching category id from this list:
        {$list}

        Grievance description:
        "{$description}"

        Respond with ONLY valid JSON, no markdown fences, no extra text:
        {"category_id": <int>, "confidence": <float between 0 and 1>, "reason": "<short reason>"}
        PROMPT;
    }

    /** @return array{category_id: ?int, confidence: float, reason: string} */
    protected function parseJson(?string $text): array
    {
        if (! $text) {
            return ['category_id' => null, 'confidence' => 0.0, 'reason' => 'empty_response'];
        }

        $clean = trim(preg_replace('/```json|```/i', '', $text));
        $decoded = json_decode($clean, true);

        if (! is_array($decoded) || ! array_key_exists('category_id', $decoded)) {
            return ['category_id' => null, 'confidence' => 0.0, 'reason' => 'parse_failed'];
        }

        return [
            'category_id' => $decoded['category_id'] !== null ? (int) $decoded['category_id'] : null,
            'confidence' => (float) ($decoded['confidence'] ?? 0),
            'reason' => (string) ($decoded['reason'] ?? ''),
        ];
    }
}

