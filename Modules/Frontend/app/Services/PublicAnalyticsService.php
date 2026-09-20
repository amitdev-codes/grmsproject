<?php

namespace Modules\Frontend\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Grievance\Enums\GrievanceStatus;
use Modules\Grievance\Models\Grievance;

class PublicAnalyticsService
{
    public function landingData(): array
    {
        return Cache::remember(
            'public_landing_stats',
            now()->addMinutes(5),
            fn (): array => $this->buildLandingData(),
        );
    }

    public function summary(): array
    {
        return ['data' => $this->landingData()];
    }

    private function buildLandingData(): array
    {
        $records = Grievance::query()
            ->where('created_at', '>=', now()->startOfYear())
            ->get(['created_at', 'status', 'closed_at']);

        $monthly = array_fill(1, 12, ['received' => 0, 'resolved' => 0]);
        $resolvedStatuses = [
            GrievanceStatus::Resolved->value,
            GrievanceStatus::Closed->value,
        ];
        $inProgressStatuses = [
            GrievanceStatus::InProgress->value,
            GrievanceStatus::Escalated->value,
        ];
        $status = [
            'statusResolved' => 0,
            'statusInProgress' => 0,
            'statusPending' => 0,
        ];
        $resolved = 0;

        foreach ($records as $record) {
            $month = (int) $record->created_at->format('n');
            $isResolved = in_array($record->status, $resolvedStatuses, true) || $record->closed_at !== null;

            $monthly[$month]['received']++;

            if ($isResolved) {
                $monthly[$month]['resolved']++;
                $resolved++;
                $status['statusResolved']++;

                continue;
            }

            if (in_array($record->status, $inProgressStatuses, true)) {
                $status['statusInProgress']++;
            } else {
                $status['statusPending']++;
            }
        }

        $total = $records->count();
        $months = [];

        foreach ($monthly as $month => $values) {
            $months[] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'received' => $values['received'],
                'resolved' => $values['resolved'],
            ];
        }

        return [
            'monthly' => $months,
            'status' => collect($status)
                ->map(fn (int $value, string $key): array => ['key' => $key, 'value' => $value])
                ->values()
                ->all(),
            'resolutionRate' => $total > 0 ? round(($resolved / $total) * 100, 1) : 0,
            'total' => $total,
        ];
    }
}
