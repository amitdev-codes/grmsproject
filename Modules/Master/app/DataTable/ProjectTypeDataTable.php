<?php

namespace Modules\Master\DataTable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Master\Models\ProjectType;

class ProjectTypeDataTable extends BaseDataTable
{
    protected string $model = ProjectType::class;

    protected array $searchableColumns = ['code', 'name', 'name_st'];

    protected array $filterableColumns = ['is_active'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'code', 'name', 'sort_order', 'created_at'];

    protected array $exportColumns = [
        'code' => 'Code',
        'name' => 'Name',
        'name_st' => 'Name(st)',
        'sort_order' => 'Sort Order',
        'is_active' => 'Active',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'sort_order';

    protected string $defaultSortDirection = 'asc';

    protected array $with = [];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        parent::applyFilter($query, $column, $value);
    }
}