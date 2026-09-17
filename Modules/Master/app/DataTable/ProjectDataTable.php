<?php

namespace Modules\Master\DataTable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Master\Models\Project;

class ProjectDataTable extends BaseDataTable
{
    protected string $model = Project::class;

    protected array $searchableColumns = ['code', 'title', 'description'];

    protected array $filterableColumns = ['status', 'district_id', 'project_type_id', 'contractor_id'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'code', 'title', 'contract_amount', 'starts_on', 'created_at'];

    protected array $exportColumns = [
        'code' => 'Code',
        'title' => 'Title',
        'status' => 'Status',
        'contract_amount' => 'Contract Amount',
        'currency' => 'Currency',
        'starts_on' => 'Starts On',
        'expected_completion_on' => 'Expected Completion',
        'completed_on' => 'Completed On',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected array $with = ['projectType', 'district', 'contractor'];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        parent::applyFilter($query, $column, $value);
    }
}