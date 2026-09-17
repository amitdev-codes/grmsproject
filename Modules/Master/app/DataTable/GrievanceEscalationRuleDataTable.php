<?php

namespace Modules\Master\DataTable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Master\Models\GrievanceEscalationRule;

class GrievanceEscalationRuleDataTable extends BaseDataTable
{
    protected string $model = GrievanceEscalationRule::class;

    protected array $searchableColumns = ['target_role'];

    protected array $filterableColumns = ['escalation_level', 'is_active', 'requires_manual_review'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'escalation_level', 'breach_after_hours', 'created_at'];

    protected array $exportColumns = [
        'escalation_level' => 'Level',
        'breach_after_hours' => 'Breach After (hrs)',
        'extension_hours' => 'Extension (hrs)',
        'target_role' => 'Target Role',
        'requires_manual_review' => 'Manual Review',
        'is_active' => 'Active',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'escalation_level';

    protected string $defaultSortDirection = 'asc';

    protected array $with = ['slaPolicy'];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        parent::applyFilter($query, $column, $value);
    }
}