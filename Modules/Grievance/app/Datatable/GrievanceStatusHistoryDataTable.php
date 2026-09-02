<?php

namespace Modules\Grievance\Datatable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Grievance\Models\GrievanceStatusHistory;

class GrievanceStatusHistoryDataTable extends BaseDataTable
{
    protected string $model = GrievanceStatusHistory::class;

    protected array $searchableColumns = [
        'note',
    ];

    protected array $filterableColumns = [
        'grievance_id', 'from_status', 'to_status', 'changed_by',
    ];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = [
        'id', 'created_at', 'to_status',
    ];

    protected array $exportColumns = [
        'grievance.reference_number' => 'Grievance',
        'from_status' => 'From Status',
        'to_status' => 'To Status',
        'changedBy.name' => 'Changed By',
        'note' => 'Note',
        'created_at' => 'Recorded At',
    ];

    protected string $defaultSort = 'created_at';
    protected string $defaultSortDirection = 'desc';

    protected array $with = ['grievance', 'changedBy'];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        $intColumns = ['grievance_id', 'changed_by'];

        if (in_array($column, $intColumns, true)) {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map('intval', $values));
            return;
        }

        if (in_array($column, ['from_status', 'to_status'], true)) {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, $values);
            return;
        }

        parent::applyFilter($query, $column, $value);
    }
}
