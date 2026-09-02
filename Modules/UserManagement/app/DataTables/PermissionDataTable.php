<?php

namespace Modules\UserManagement\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionDataTable extends BaseDataTable
{
    protected string $model = Permission::class;

    protected array $searchableColumns = ['name'];

    protected array $filterableColumns = [];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'name', 'created_at'];
    protected array $exportColumns = [
        'name' => 'Name',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {

        parent::applyFilter($query, $column, $value);
    }
}
