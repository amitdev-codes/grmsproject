<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DynamicExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param Collection $rows    Rows to export (Eloquent models or arrays)
     * @param array      $columns ['db_column' => 'Human Readable Heading']
     */
    public function __construct(
        protected Collection $rows,
        protected array $columns
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
            ->map(fn ($column) => data_get($row, $column))
            ->toArray();
    }
}
