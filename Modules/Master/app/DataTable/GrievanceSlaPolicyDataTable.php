<?php

namespace Modules\Master\DataTable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Master\Models\GrievanceSlaPolicy;

class GrievanceSlaPolicyDataTable extends BaseDataTable
{
    protected string $model = GrievanceSlaPolicy::class;

    protected array $searchableColumns = ['code', 'name', 'priority'];

    protected array $filterableColumns = ['priority', 'is_active'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'code', 'name', 'acknowledgement_hours', 'resolution_hours', 'created_at'];

    protected array $exportColumns = [
        'code' => 'Code',
        'name' => 'Name',
        'priority' => 'Priority',
        'acknowledgement_hours' => 'Acknowledgement (hrs)',
        'resolution_hours' => 'Resolution (hrs)',
        'use_business_hours' => 'Business Hours',
        'is_active' => 'Active',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'name';

    protected string $defaultSortDirection = 'asc';

    protected array $with = [];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        parent::applyFilter($query, $column, $value);
    }
}