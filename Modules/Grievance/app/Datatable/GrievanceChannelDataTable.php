<?php

namespace Modules\Grievance\Datatable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Grievance\Models\GrievanceChannel;

class GrievanceChannelDataTable extends BaseDataTable
{
    protected string $model = GrievanceChannel::class;

    protected array $searchableColumns = ['code', 'name','is_active'];

    protected array $filterableColumns = ['is_active'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'name', 'code','created_at'];

    protected array $exportColumns = [
        'code' => 'Code',
        'name' => 'Name',
        'is_active' => 'IS Active',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {

        if ($column === 'is_active') {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map(fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN), $values));

            return;
        }

        parent::applyFilter($query, $column, $value);
    }
}

