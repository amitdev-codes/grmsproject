<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DataTableImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    /** @var array<int, array<string, mixed>> Valid, mapped rows ready for the service to persist. */
    public array $imported = [];

    /** @var array<int, array{row: int, errors: array}> */
    public array $failures = [];

    /**
     * @param  array<string,string>  $map    ['target_attribute' => 'source_header']
     * @param  array<string,string>  $rules  Laravel validation rules keyed by target attribute
     */
    public function __construct(
        protected array $map,
        protected array $rules,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $mapped = [];

            foreach ($this->map as $attribute => $header) {
                $mapped[$attribute] = $row[$header] ?? null;
            }

            $validator = Validator::make($mapped, $this->rules);

            if ($validator->fails()) {
                $this->failures[] = [
                    // +2: heading row + zero-based index
                    'row' => $index + 2,
                    'errors' => $validator->errors()->toArray(),
                ];

                continue;
            }

            $this->imported[] = $mapped;
        }
    }
}
