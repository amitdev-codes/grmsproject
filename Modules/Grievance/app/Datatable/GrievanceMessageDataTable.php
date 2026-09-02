<?php

namespace Modules\Grievance\Datatable;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Grievance\Models\GrievanceMessage;

class GrievanceMessageDataTable extends BaseDataTable
{
    protected string $model = GrievanceMessage::class;

    protected array $searchableColumns = [
        'message',
    ];

    protected array $filterableColumns = [
        'sender_type', 'is_internal', 'grievance_id', 'user_id',
    ];

    protected array $textFilterColumns = [];

    protected array $sortableColumns = [
        'id', 'sender_type', 'created_at', 'updated_at',
    ];

    protected array $exportColumns = [
        'grievance.reference_number' => 'Grievance',
        'sender_type' => 'Sender Type',
        'user.name' => 'Sender',
        'message' => 'Message',
        'is_internal' => 'Internal Only',
        'created_at' => 'Sent At',
    ];

    protected string $defaultSort = 'created_at';
    protected string $defaultSortDirection = 'desc';

    protected array $with = ['grievance', 'user'];

    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        $intColumns = ['grievance_id', 'user_id'];

        if (in_array($column, $intColumns, true)) {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map('intval', $values));
            return;
        }

        if ($column === 'sender_type') {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, $values);
            return;
        }

        if ($column === 'is_internal') {
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($column, array_map(fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN), $values));
            return;
        }

        parent::applyFilter($query, $column, $value);
    }
}
