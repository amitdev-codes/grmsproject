<?php

namespace Modules\Master\DataTable;

use App\DataTables\BaseDataTable;
use Modules\Master\Models\Division;
use Illuminate\Database\Eloquent\Builder;
class DivisionDataTable extends BaseDataTable
{
    protected string $model = Division::class;

    protected array $searchableColumns = ['code', 'name', 'name_st', 'description'];

    protected array $filterableColumns = ['name'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'created_at'];

    protected array $exportColumns = [
        'code' => 'Code',
        'name' => 'Name',
        'name_st' => 'Name(st)',
        'description'=>'Description',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected array $with = [];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        parent::applyFilter($query, $column, $value);
    }
}
