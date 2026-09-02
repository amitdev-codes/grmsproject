<?php

namespace Modules\Master\DataTable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Master\Models\Section;

class SectionDataTable extends BaseDataTable
{
    protected string $model = Section::class;

    protected array $searchableColumns = ['code', 'name', 'name_st'];

    protected array $filterableColumns = ['division_id', 'name'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'created_at'];

    protected array $exportColumns = [
        'division.name' => 'Division',
        'code' => 'Code',
        'name' => 'Name',
        'name_st' => 'Name(st)',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected array $with = ['division'];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        $intColumns = ['division_id'];
        if (in_array($column, $intColumns, true)) {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map('intval', $values));

            return;
        }
        parent::applyFilter($query, $column, $value);
    }
}
