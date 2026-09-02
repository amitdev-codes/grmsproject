<?php

namespace Modules\Grievance\Datatable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Grievance\Models\GrievanceEscalation;

class GrievanceEscalationDataTable extends BaseDataTable
{
    protected string $model = GrievanceEscalation::class;

    protected array $searchableColumns = [
        'reason',
    ];

    protected array $filterableColumns = [
        'escalation_level', 'escalated_to', 'resolved', 'grievance_id',
    ];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = [
        'id', 'escalation_level', 'sla_breached_at', 'escalated_at', 'resolved', 'created_at',
    ];

    protected array $exportColumns = [
        'grievance.reference_number' => 'Grievance Ref. No.',
        'escalation_level' => 'Escalation Level',
        'escalatedOfficer' => 'Escalated To',
        'sla_breached_at' => 'SLA Breached At',
        'escalated_at' => 'Escalated At',
        'reason' => 'Reason',
        'resolved' => 'Resolved',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'escalated_at';
    protected string $defaultSortDirection = 'desc';

    protected array $with = ['grievance', 'escalatedOfficer'];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        $intColumns = ['escalation_level', 'escalated_to', 'grievance_id'];

        if (in_array($column, $intColumns, true)) {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map('intval', $values));
            return;
        }

        if ($column === 'resolved') {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map(fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN), $values));
            return;
        }

        parent::applyFilter($query, $column, $value);
    }
}
