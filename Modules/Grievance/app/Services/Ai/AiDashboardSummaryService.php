<?php

namespace Modules\Grievance\Services\Ai;

use Illuminate\Support\Facades\Log;
use Modules\Grievance\Services\Ai\Drivers\AnthropicDriver;
use Modules\Grievance\Services\Ai\Drivers\GeminiDriver;
use Modules\Grievance\Services\Ai\Drivers\GrokDriver;
use Modules\Grievance\Services\Ai\Drivers\OpenAiDriver;

class AiDashboardSummaryService
{
    public function generate(array $dashboardData): string
    {
        if (! config('grievance.ai.enabled', false)) {
            throw new AiSummaryException('AI dashboard summaries are not enabled.');
        }

        $reportData = array_intersect_key($dashboardData, array_flip([
            'summary',
            'monthly_trends',
            'by_issue_type',
            'by_region',
            'by_channel',
            'contractor_scores',
            'compliance',
        ]));

        try {
            $data = json_encode($reportData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            Log::error('Unable to encode dashboard data for AI summary.', ['exception' => $exception]);
            throw new AiSummaryException('Dashboard data could not be prepared for an AI summary.', previous: $exception);
        }

        $prompt = <<<PROMPT
        You are an analytical assistant writing a concise management summary for a grievance redressal dashboard.
        Use only the aggregate figures provided. Do not infer causes, make up facts, identify individuals, or recommend case decisions.
        Return 3 to 5 short bullet points covering workload, resolution/compliance trends, and notable category or regional patterns when supported by the data.

        Aggregate dashboard data:
        {$data}
        PROMPT;

        return $this->driver()->generate($prompt);
    }

    private function driver(): AiTextGeneratorInterface
    {
        return match (config('grievance.ai.provider', 'anthropic')) {
            'openai' => app(OpenAiDriver::class),
            'gemini' => app(GeminiDriver::class),
            'grok' => app(GrokDriver::class),
            default => app(AnthropicDriver::class),
        };
    }
}
