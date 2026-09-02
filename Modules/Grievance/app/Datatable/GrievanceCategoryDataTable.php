<?php

namespace Modules\Grievance\Datatable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Grievance\Models\GrievanceCategory;

class GrievanceCategoryDataTable extends BaseDataTable
{
    protected string $model = GrievanceCategory::class;

    protected array $searchableColumns = ['name_en', 'name_st', 'slug', 'is_sensitive', 'is_active'];

    protected array $filterableColumns = ['is_active', 'is_sensitive'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'name_en', 'name_st', 'slug', 'created_at'];

    protected array $exportColumns = [
        'name_en' => 'Name',
        'name_st' => 'Name St',
        'slug' => 'Slug',
        'is_sensitive' => 'IS Sensitive',
        'is_active' => 'IS Active',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {

        if ($column === 'is_sensitive') {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map(fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN), $values));

            return;
        }
        if ($column === 'is_active') {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map(fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN), $values));

            return;
        }

        parent::applyFilter($query, $column, $value);
    }
}
