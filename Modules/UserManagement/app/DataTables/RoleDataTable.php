<?php

namespace Modules\UserManagement\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleDataTable extends BaseDataTable
{
    protected string $model = Role::class;
    protected array $searchableColumns = ['code', 'name', 'name_st'];
    protected array $filterableColumns = ['status'];
    protected array $textFilterColumns = [];
    protected array $sortableColumns = ['id', 'code', 'name', 'name_st', 'status', 'created_at'];
    protected array $exportColumns = [
        'code' => 'Code',
        'name' => 'Name',
        'name_st' => 'Name ST',
        'permissions' => 'Permissions',
        'status' => 'Status',
        'created_at' => 'Created At',
    ];
    protected string $defaultSort = 'created_at';
    protected string $defaultSortDirection = 'desc';
    protected array $with = ['permissions'];
    protected array $withCount = ['users'];

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $query->where(function (Builder $q) use ($search): void {
            foreach ($this->searchableColumns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }

            $q->orWhereHas('permissions', function (Builder $permQuery) use ($search) {
                $permQuery->where('name', 'like', "%{$search}%");
            });
        });
    }

    /** "status" isn't a plain whereIn-able column, so handle it explicitly. */
    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        if ($column === 'status') {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map(fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN), $values));
            return;
        }
        parent::applyFilter($query, $column, $value);
    }
}
