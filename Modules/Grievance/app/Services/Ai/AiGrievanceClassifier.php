<?php

namespace Modules\Grievance\Services\Ai;

use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Services\Ai\Drivers\AnthropicDriver;
use Modules\Grievance\Services\Ai\Drivers\GeminiDriver;
use Modules\Grievance\Services\Ai\Drivers\GrokDriver;
use Modules\Grievance\Services\Ai\Drivers\OpenAiDriver;

class AiGrievanceClassifier
{
    public function driver(): AiClassifierDriverInterface
    {
        return match (config('grievance.ai.provider')) {
            'openai' => app(OpenAiDriver::class),
            'gemini' => app(GeminiDriver::class),
            'grok' => app(GrokDriver::class),
            default => app(AnthropicDriver::class),
        };
    }

    /** @return array{category_id: ?int, confidence: float, reason: string} */
    public function classifyToCategory(string $description): array
    {
        if (! config('grievance.ai.enabled')) {
            return ['category_id' => null, 'confidence' => 0.0, 'reason' => 'ai_disabled'];
        }

        // Adjust the column alias below if your categories table uses a
        // different name column (e.g. name_en instead of name).
        $categories = GrievanceCategory::query()
            ->where('is_active', true)
            ->get(['id', 'name'])
            ->toArray();

        if (empty($categories)) {
            return ['category_id' => null, 'confidence' => 0.0, 'reason' => 'no_categories'];
        }

        try {
            return $this->driver()->classify($description, $categories);
        } catch (\Throwable $e) {
            report($e);

            // Never let an AI/network failure block grievance registration —
            // it just falls back to manual/rule-based routing downstream.
            return ['category_id' => null, 'confidence' => 0.0, 'reason' => 'exception'];
        }
    }
}

