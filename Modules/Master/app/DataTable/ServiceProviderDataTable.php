<?php

namespace Modules\Master\DataTable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Master\Models\ServiceProvider;

class ServiceProviderDataTable extends BaseDataTable
{
    protected string $model = ServiceProvider::class;

    protected array $searchableColumns = ['code', 'name', 'contact_name', 'phone', 'email'];

    protected array $filterableColumns = ['provider_type', 'is_active'];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = ['id', 'code', 'name', 'provider_type', 'created_at'];

    protected array $exportColumns = [
        'code' => 'Code',
        'name' => 'Name',
        'provider_type' => 'Type',
        'contact_name' => 'Contact',
        'phone' => 'Phone',
        'email' => 'Email',
        'is_active' => 'Active',
        'created_at' => 'Created At',
    ];

    protected string $defaultSort = 'name';

    protected string $defaultSortDirection = 'asc';

    protected array $with = [];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        parent::applyFilter($query, $column, $value);
    }
}