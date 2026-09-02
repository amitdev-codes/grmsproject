<?php

namespace Modules\Grievance\Datatable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Grievance\Models\Resolution;

class ResolutionDataTable extends BaseDataTable
{
    protected string $model = Resolution::class;

    protected array $searchableColumns = [
        'resolution_text', 'rejected_reason',
    ];

    protected array $filterableColumns = [
        'grievance_id', 'proposed_by', 'approved_by',
    ];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = [
        'id', 'created_at', 'approved_at', 'complainant_confirmed_at',
    ];

    protected array $exportColumns = [
        'grievance.reference_number' => 'Grievance',
        'resolution_text' => 'Resolution',
        'proposedBy.name' => 'Proposed By',
        'approvedByUser.name' => 'Approved By',
        'approved_at' => 'Approved At',
        'complainant_confirmed_at' => 'Confirmed At',
        'rejected_reason' => 'Rejected Reason',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'created_at';
    protected string $defaultSortDirection = 'desc';

    protected array $with = ['grievance', 'proposedBy', 'approvedByUser'];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        $intColumns = ['grievance_id', 'proposed_by', 'approved_by'];

        if (in_array($column, $intColumns, true)) {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map('intval', $values));
            return;
        }

        if ($column === 'state') {
            // Derived filter — not a real column, computed from three
            // nullable timestamp/text columns. Map UI values to conditions.
            $values = is_array($value) ? $value : [$value];
            $query->where(function (Builder $q) use ($values) {
                foreach ($values as $v) {
                    $q->orWhere(function (Builder $sub) use ($v) {
                        match ($v) {
                            'rejected' => $sub->whereNotNull('rejected_reason'),
                            'confirmed' => $sub->whereNotNull('complainant_confirmed_at'),
                            'approved' => $sub->whereNotNull('approved_at')->whereNull('complainant_confirmed_at')->whereNull('rejected_reason'),
                            'proposed' => $sub->whereNull('approved_at')->whereNull('rejected_reason'),
                            default => null,
                        };
                    });
                }
            });
            return;
        }

        parent::applyFilter($query, $column, $value);
    }
}
