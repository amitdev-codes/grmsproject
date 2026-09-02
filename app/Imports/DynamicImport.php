<?php

namespace App\Imports;

use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class DynamicImport implements OnEachRow, WithHeadingRow, WithValidation
{
    /**
     * @param string $model        Fully qualified model class
     * @param array  $map          ['spreadsheet_header' => 'model_attribute']
     * @param array  $rules        Laravel validation rules keyed by spreadsheet_header
     */
    public function __construct(
        protected string $model,
        protected array $map = [],
        protected array $rules = []
    ) {}

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($this->map)) {
            $this->model::create($data);
            return;
        }

        $attributes = [];
        foreach ($this->map as $sheetColumn => $modelAttribute) {
            $attributes[$modelAttribute] = $data[$sheetColumn] ?? null;
        }

        $this->model::create($attributes);
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
