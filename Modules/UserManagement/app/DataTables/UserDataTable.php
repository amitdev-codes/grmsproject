<?php

namespace Modules\UserManagement\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
class UserDataTable extends BaseDataTable
{
    protected string $model = User::class;

    protected array $searchableColumns = [ 'name', 'username', 'email', 'phone'];

    protected array $filterableColumns = ['status', 'email', 'role'];

    protected array $textFilterColumns = ['email'];

    protected array $sortableColumns = ['id', 'name', 'username', 'email', 'phone', 'status', 'created_at'];

    protected array $exportColumns = [
        'name' => 'Name',
        'username' => 'Username',
        'email' => 'Email',
        'phone' => 'Phone',
        'role_names' => 'Role',
        'status' => 'Status',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected array $with = ['roles', 'media', 'district', 'division', 'section'];

    /** "role" and "status" aren't plain whereIn-able columns, so handle them explicitly. */
    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        if ($column === 'role') {
            $roles = is_array($value) ? $value : [$value];
            $query->whereHas('roles', fn (Builder $q) => $q->whereIn('name', $roles));

            return;
        }

        if ($column === 'status') {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map(fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN), $values));

            return;
        }

        parent::applyFilter($query, $column, $value);
    }
}

