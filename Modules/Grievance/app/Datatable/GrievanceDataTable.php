<?php

namespace Modules\Grievance\Datatable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Grievance\Models\Grievance;

class GrievanceDataTable extends BaseDataTable
{
    protected string $model = Grievance::class;

    protected array $searchableColumns = [
        'reference_number', 'description', 'contact_name', 'contact_email', 'contact_phone',
    ];

    protected array $filterableColumns = [
        'status', 'priority', 'category_id', 'district_id', 'division_id',
        'section_id', 'assigned_officer_id', 'is_anonymous', 'submitted_via',
    ];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = [
        'id', 'reference_number', 'status', 'priority', 'created_at', 'sla_due_at', 'resolved_at',
    ];

    protected array $exportColumns = [
        'reference_number' => 'Reference No.',
        'status' => 'Status',
        'priority' => 'Priority',
        'category.name_en' => 'Category',
        'district.name_en' => 'District',
        'division.name' => 'Division',
        'section.name' => 'Section',
        'assignedOfficer.name' => 'Assigned Officer',
        'contact_name' => 'Complainant',
        'submitted_via' => 'Submitted Via',
        'created_at' => 'Submitted At',
        'sla_due_at' => 'SLA Due',
        'resolved_at' => 'Resolved At',
    ];

    protected string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected array $with = ['category', 'district', 'division', 'section', 'assignedOfficer', 'user'];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        $intColumns = ['category_id', 'district_id', 'division_id', 'section_id', 'assigned_officer_id'];
        if (in_array($column, $intColumns, true)) {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map('intval', $values));

            return;
        }

        if (in_array($column, ['status', 'priority', 'submitted_via'], true)) {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, $values);

            return;
        }

        if ($column === 'is_anonymous') {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map(fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN), $values));

            return;
        }

        parent::applyFilter($query, $column, $value);
    }
}
