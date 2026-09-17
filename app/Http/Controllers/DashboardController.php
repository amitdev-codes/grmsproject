<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\Enums\GrievanceStatus;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceChannel;
use Modules\Master\Models\District;

class DashboardController
{
    public function index(): Response
    {
        return Inertia::render('dashboard/index', $this->dashboardData());
    }

    public function analytics(): Response
    {
        return Inertia::render('dashboard/analytics', $this->dashboardData());
    }

    public function reports(): Response
    {
        return Inertia::render('dashboard/reports', $this->dashboardData());
    }

    public function data(): array
    {
        return $this->dashboardData();
    }

    protected function dashboardData(): array
    {
        $total = Grievance::count();
        $resolved = Grievance::where('status', GrievanceStatus::Resolved->value)->count();
        $inProgress = Grievance::where('status', GrievanceStatus::InProgress->value)->count();
        $pending = Grievance::whereIn('status', [
            GrievanceStatus::Submitted->value,
            GrievanceStatus::Acknowledged->value,
            GrievanceStatus::AllocatedDivision->value,
            GrievanceStatus::AllocatedSection->value,
            GrievanceStatus::AssignedOfficer->value,
            GrievanceStatus::Assigned->value,
        ])->count();
        $escalated = Grievance::where('status', GrievanceStatus::Escalated->value)->count();
        $closed = Grievance::where('status', GrievanceStatus::Closed->value)->count();
        $rejected = Grievance::where('status', GrievanceStatus::Rejected->value)->count();

        // Trends by month (last 12 months)
        $monthlyTrends = Grievance::selectRaw('
                EXTRACT(YEAR FROM created_at) as year,
                EXTRACT(MONTH FROM created_at) as month,
                count(*) as count
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($row) {
                $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                return [
                    'month' => $months[(int) $row->month] . ' ' . $row->year,
                    'count' => $row->count,
                ];
            });

        // Trends by issue type (category)
        $byIssueType = GrievanceCategory::withCount('grievances')
            ->get()
            ->map(fn ($c) => ['name' => $c->name_en, 'count' => $c->grievances_count]);

        // Trends by region (district)
        $byRegion = District::withCount('grievances')
            ->get()
            ->map(fn ($d) => ['name' => $d->name, 'count' => $d->grievances_count]);

        // Heatmap data: district x category matrix
        $heatmap = DB::table('grievances')
            ->join('grievance_categories', 'grievances.grievance_category_id', '=', 'grievance_categories.id')
            ->join('districts', 'grievances.district_id', '=', 'districts.id')
            ->selectRaw('districts.name as district, grievance_categories.name_en as category, count(*) as count')
            ->groupBy('districts.name', 'grievance_categories.name_en')
            ->get();

        // Contractor-wise performance score (via projects → contractor)
        $contractorScores = DB::table('grievances')
            ->join('projects', 'grievances.project_id', '=', 'projects.id')
            ->join('service_providers as sp', 'projects.contractor_id', '=', 'sp.id')
            ->selectRaw('sp.name as name, count(*) as total,
                SUM(CASE WHEN grievances.status = ? THEN 1 ELSE 0 END) as resolved',
                [GrievanceStatus::Resolved->value])
            ->groupBy('sp.name')
            ->get()
            ->map(function ($row) {
                $rate = $row->total > 0 ? round(($row->resolved / $row->total) * 100, 1) : 0;

                return [
                    'name' => $row->name,
                    'total' => $row->total,
                    'resolved' => $row->resolved,
                    'score' => $rate,
                ];
            })
            ->sortByDesc('score')
            ->values();

        // Monthly compliance reports (resolved / total rate)
        $compliance = Grievance::selectRaw('
                EXTRACT(YEAR FROM created_at) as year,
                EXTRACT(MONTH FROM created_at) as month,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as resolved,
                count(*) as total
            ', [GrievanceStatus::Resolved->value])
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($row) {
                $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $rate = $row->total > 0 ? round(($row->resolved / $row->total) * 100, 1) : 0;

                return [
                    'month' => $months[(int) $row->month] . ' ' . $row->year,
                    'resolved' => $row->resolved,
                    'total' => $row->total,
                    'rate' => $rate,
                ];
            });

        // Intake channels
        $byChannel = GrievanceChannel::withCount('grievances')
            ->get()
            ->map(fn ($c) => ['name' => $c->name, 'count' => $c->grievances_count]);

        // Summary cards
        $summary = [
            'total' => $total,
            'resolved' => $resolved,
            'pending' => $pending,
            'closed' => $closed,
            'rejected' => $rejected,
            'escalated' => $escalated,
            'in_progress' => $inProgress,
            'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100, 1) : 0,
        ];

        return [
            'summary' => $summary,
            'monthly_trends' => $monthlyTrends,
            'by_issue_type' => $byIssueType,
            'by_region' => $byRegion,
            'by_channel' => $byChannel,
            'heatmap' => $heatmap,
            'contractor_scores' => $contractorScores,
            'compliance' => $compliance,
        ];
    }
}
