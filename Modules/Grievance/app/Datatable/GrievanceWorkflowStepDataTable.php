<?php

namespace Modules\Grievance\Datatable;

use App\DataTables\BaseDataTable;
use Modules\Grievance\Models\GrievanceWorkflowStep;

class GrievanceWorkflowStepDataTable extends BaseDataTable
{
    protected string $model = GrievanceWorkflowStep::class;

    protected array $searchableColumns = ['name', 'role_name', 'workflow_key'];

    protected array $filterableColumns = ['is_active', 'is_final_approval'];

    protected array $sortableColumns = ['step_number', 'name', 'role_name', 'is_active'];

    protected string $defaultSort = 'step_number';

    protected string $defaultSortDirection = 'asc';

    protected array $with = ['approverUser'];

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::query()->where('workflow_key', 'default');
    }
}
