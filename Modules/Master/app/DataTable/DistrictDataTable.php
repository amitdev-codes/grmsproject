<?php

namespace Modules\Master\DataTable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Master\Models\District;

class DistrictDataTable extends BaseDataTable
{
    protected string $model = District::class;

    protected array $searchableColumns = ['code', 'name', 'name_st'];

    protected array $filterableColumns = ['name'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'created_at'];

    protected array $exportColumns = [
        'code' => 'Code',
        'name' => 'Name',
        'name_st' => 'Name(st)',
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
