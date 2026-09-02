<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DataTableExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection  $rows
     * @param  array<string,string>  $columns  ['attribute' => 'Column Heading'], supports dot-notation
     *                                         attributes (e.g. 'district.name') and accessor-style
     *                                         attributes defined on the model (e.g. 'role_names').
     */
    public function __construct(
        protected Collection $rows,
        protected array $columns,
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function map($row): array
    {
        return collect(array_keys($this->columns))
            ->map(function (string $attribute) use ($row) {
                $value = data_get($row, $attribute);

                return match (true) {
                    $value instanceof Collection => $value->pluck('name')->implode(', '),
                    is_array($value) => implode(', ', $value),
                    is_bool($value) => $value ? 'Yes' : 'No',
                    default => (string) $value,
                };
            })
            ->toArray();
    }
}
