<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    private const array FINAL_STATUSES = ['resolved', 'closed'];

    private const array NON_PENDING_STATUSES = ['resolved', 'closed', 'rejected'];

    public function index(): RedirectResponse
    {
        return redirect()->route('report.summary');
    }

    public function summary(Request $request): Response
    {
        $filters = $this->filters($request, true);
        $base = $this->filteredGrievances($filters);
        $dateParts = $this->dateParts();
        $yearExpression = $dateParts['year'];
        $monthExpression = $dateParts['month'];
        $groupBy = $filters['group_by'];
        $query = clone $base;

        if ($groupBy === 'month') {
            $query->selectRaw("{$yearExpression} as year, {$monthExpression} as month")
                ->selectRaw("{$yearExpression} * 100 + {$monthExpression} as sort_key")
                ->groupByRaw("{$yearExpression}, {$monthExpression}");
        } elseif ($groupBy === 'division') {
            $query->leftJoin('divisions', 'grievances.division_id', '=', 'divisions.id')
                ->selectRaw("COALESCE(divisions.name, 'Unassigned') as group_name")
                ->groupBy('divisions.id', 'divisions.name');
        } elseif ($groupBy === 'category') {
            $query->join('grievance_categories', 'grievances.grievance_category_id', '=', 'grievance_categories.id')
                ->select('grievance_categories.name_en as group_name')
                ->groupBy('grievance_categories.id', 'grievance_categories.name_en');
        } else {
            $query->selectRaw("{$yearExpression} as group_name")
                ->groupByRaw($yearExpression);
        }

        $rows = $query
            ->selectRaw('COUNT(grievances.id) as total')
            ->selectRaw($this->pendingCountExpression('grievances.status', 'grievances.id').' as pending')
            ->selectRaw($this->resolvedCountExpression('grievances.status', 'grievances.id').' as resolved');

        if ($groupBy === 'month') {
            $rows->orderByDesc('sort_key');
        } elseif ($groupBy === 'year') {
            $rows->orderByDesc('group_name');
        } else {
            $rows->orderBy('group_name');
        }

        $rows = $rows->get()->map(function ($row) use ($groupBy) {
            $group = match ($groupBy) {
                'month' => sprintf('%04d-%02d', $row->year, $row->month),
                default => $row->group_name,
            };

            return [
                'group' => $group,
                'total' => (int) $row->total,
                'pending' => (int) $row->pending,
                'resolved' => (int) $row->resolved,
            ];
        });

        $totals = (clone $base)
            ->selectRaw('COUNT(grievances.id) as total')
            ->selectRaw($this->pendingCountExpression('grievances.status', 'grievances.id').' as pending')
            ->selectRaw($this->resolvedCountExpression('grievances.status', 'grievances.id').' as resolved')
            ->first();

        return Inertia::render('Report::Reports/Summary', [
            'totals' => [
                'total' => (int) $totals->total,
                'pending' => (int) $totals->pending,
                'resolved' => (int) $totals->resolved,
            ],
            'rows' => $rows,
            'groupBy' => $groupBy,
            ...$this->filterProps($filters),
        ]);
    }

    public function annex(Request $request): Response
    {
        $filters = $this->filters($request);

        return Inertia::render('Report::Reports/Annex', [
            'byCategory' => $this->categoryCounts($filters),
            ...$this->filterProps($filters),
        ]);
    }

    public function detailed(Request $request): Response
    {
        $filters = $this->filters($request);
        $grievances = $this->filteredGrievances($filters)
            ->leftJoin('grievance_categories', 'grievances.grievance_category_id', '=', 'grievance_categories.id')
            ->leftJoin('divisions', 'grievances.division_id', '=', 'divisions.id')
            ->where(function ($query) {
                $query->whereIn('grievances.status', self::FINAL_STATUSES)
                    ->orWhereExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('resolutions')
                            ->whereColumn('resolutions.grievance_id', 'grievances.id')
                            ->whereNotNull('resolutions.approved_at')
                            ->whereNull('resolutions.rejected_reason')
                            ->whereNull('resolutions.deleted_at');
                    });
            })
            ->select([
                'grievances.id',
                'grievances.reference_no',
                'grievances.status',
                'grievances.created_at',
                'grievances.closed_at',
                'grievance_categories.name_en as category',
                'divisions.name as division',
            ])
            ->selectSub(
                DB::table('resolutions')
                    ->selectRaw('MAX(approved_at)')
                    ->whereColumn('resolutions.grievance_id', 'grievances.id')
                    ->whereNotNull('resolutions.approved_at')
                    ->whereNull('resolutions.rejected_reason')
                    ->whereNull('resolutions.deleted_at'),
                'approved_at',
            )
            ->selectSub(
                DB::table('resolutions')
                    ->join('users as approvers', 'resolutions.approved_by', '=', 'approvers.id')
                    ->select('approvers.name')
                    ->whereColumn('resolutions.grievance_id', 'grievances.id')
                    ->whereNotNull('resolutions.approved_at')
                    ->whereNull('resolutions.rejected_reason')
                    ->whereNull('resolutions.deleted_at')
                    ->orderByDesc('resolutions.approved_at')
                    ->limit(1),
                'approver',
            )
            ->orderByDesc('grievances.closed_at')
            ->orderByDesc('grievances.created_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Report::Reports/Detailed', [
            'grievances' => $grievances,
            ...$this->filterProps($filters),
        ]);
    }

    private function categoryCounts(array $filters)
    {
        $joinFilters = $filters;
        unset($joinFilters['category_id']);

        return DB::table('grievance_categories')
            ->leftJoin('grievances', function (JoinClause $join) {
                $join->on('grievances.grievance_category_id', '=', 'grievance_categories.id')
                    ->whereNull('grievances.deleted_at');
            })
            ->leftJoin('divisions', function (JoinClause $join) {
                $join->on('grievances.division_id', '=', 'divisions.id')
                    ->whereNull('divisions.deleted_at');
            })
            ->whereNull('grievance_categories.deleted_at')
            ->when($filters['category_id'], fn ($query, $value) => $query->where('grievance_categories.id', $value))
            ->when($joinFilters['district_id'], fn ($query, $value) => $query->where('grievances.district_id', $value))
            ->when($joinFilters['division_id'], fn ($query, $value) => $query->where('grievances.division_id', $value))
            ->when($joinFilters['status'], fn ($query, $value) => $query->where('grievances.status', $value))
            ->select('grievance_categories.id', 'grievance_categories.name_en as category')
            ->selectRaw('COUNT(grievances.id) as total')
            ->selectRaw($this->pendingCountExpression('grievances.status', 'grievances.id').' as pending')
            ->selectRaw($this->resolvedCountExpression('grievances.status', 'grievances.id').' as resolved')
            ->groupBy('grievance_categories.id', 'grievance_categories.name_en')
            ->orderBy('grievance_categories.name_en')
            ->get();
    }

    /**
     * @param  array{district_id: ?int, division_id: ?int, category_id: ?int, status: ?string, group_by?: string}  $filters
     */
    private function filteredGrievances(array $filters): Builder
    {
        return DB::table('grievances')
            ->whereNull('grievances.deleted_at')
            ->when($filters['district_id'], fn ($query, $value) => $query->where('grievances.district_id', $value))
            ->when($filters['division_id'], fn ($query, $value) => $query->where('grievances.division_id', $value))
            ->when($filters['category_id'], fn ($query, $value) => $query->where('grievances.grievance_category_id', $value))
            ->when($filters['status'], fn ($query, $value) => $query->where('grievances.status', $value));
    }

    /**
     * @return array{district_id: ?int, division_id: ?int, category_id: ?int, status: ?string, group_by?: string}
     */
    private function filters(Request $request, bool $includeGroup = false): array
    {
        $validated = $request->validate([
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'category_id' => ['nullable', 'integer', 'exists:grievance_categories,id'],
            'status' => ['nullable', 'string', 'in:submitted,acknowledged,allocated_division,allocated_section,reallocation_required,assigned_officer,assigned,in_progress,escalated,resolved,closed,rejected,reopened'],
            'group_by' => [$includeGroup ? 'nullable' : 'prohibited', 'in:year,month,division,category'],
        ]);

        return [
            'district_id' => isset($validated['district_id']) ? (int) $validated['district_id'] : null,
            'division_id' => isset($validated['division_id']) ? (int) $validated['division_id'] : null,
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'status' => $validated['status'] ?? null,
            ...($includeGroup ? ['group_by' => $validated['group_by'] ?? 'year'] : []),
        ];
    }

    private function filterProps(array $filters): array
    {
        return [
            'filters' => [
                'district_id' => (string) ($filters['district_id'] ?? ''),
                'division_id' => (string) ($filters['division_id'] ?? ''),
                'category_id' => (string) ($filters['category_id'] ?? ''),
                'status' => $filters['status'] ?? '',
                'group_by' => $filters['group_by'] ?? 'year',
            ],
            'options' => [
                'districts' => DB::table('districts')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
                'divisions' => DB::table('divisions')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
                'categories' => DB::table('grievance_categories')->whereNull('deleted_at')->orderBy('name_en')->get(['id', 'name_en as name']),
                'statuses' => [
                    ['value' => 'submitted', 'label' => 'Submitted'],
                    ['value' => 'acknowledged', 'label' => 'Acknowledged'],
                    ['value' => 'allocated_division', 'label' => 'Allocated to division'],
                    ['value' => 'allocated_section', 'label' => 'Allocated to section'],
                    ['value' => 'reallocation_required', 'label' => 'Reallocation required'],
                    ['value' => 'assigned_officer', 'label' => 'Assigned to officer'],
                    ['value' => 'assigned', 'label' => 'Assigned'],
                    ['value' => 'in_progress', 'label' => 'In progress'],
                    ['value' => 'escalated', 'label' => 'Escalated'],
                    ['value' => 'resolved', 'label' => 'Resolved'],
                    ['value' => 'closed', 'label' => 'Closed'],
                    ['value' => 'rejected', 'label' => 'Rejected'],
                    ['value' => 'reopened', 'label' => 'Reopened'],
                ],
            ],
        ];
    }

    private function pendingCountExpression(string $status = 'status', string $id = 'id'): string
    {
        $statuses = implode("', '", self::NON_PENDING_STATUSES);

        return "COALESCE(SUM(CASE WHEN {$id} IS NOT NULL AND {$status} NOT IN ('{$statuses}') THEN 1 ELSE 0 END), 0)";
    }

    private function resolvedCountExpression(string $status = 'status', string $id = 'id'): string
    {
        $statuses = implode("', '", self::FINAL_STATUSES);

        return "COALESCE(SUM(CASE WHEN {$id} IS NOT NULL AND {$status} IN ('{$statuses}') THEN 1 ELSE 0 END), 0)";
    }

    /**
     * @return array{year: string, month: string}
     */
    private function dateParts(): array
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [
                'year' => "CAST(strftime('%Y', created_at) AS INTEGER)",
                'month' => "CAST(strftime('%m', created_at) AS INTEGER)",
            ];
        }

        return ['year' => 'YEAR(created_at)', 'month' => 'MONTH(created_at)'];
    }
}
