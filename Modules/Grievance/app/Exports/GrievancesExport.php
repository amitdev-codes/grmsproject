<?php

namespace Modules\Grievance\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GrievancesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query)
    {
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['Reference No', 'Category', 'Channel', 'District', 'Status', 'Anonymous', 'Phone', 'Description', 'Submitted At'];
    }

    public function map($grievance): array
    {
        /** @var Grievance $grievance */
        return [
            $grievance->reference_no,
            $grievance->category?->name_en,
            $grievance->channel?->name,
            $grievance->district?->name,
            $grievance->status,
            $grievance->is_anonymous ? 'Yes' : 'No',
            $grievance->is_anonymous ? '—' : $grievance->complainant_phone,
            $grievance->description,
            $grievance->created_at->format('Y-m-d H:i'),
        ];
    }
}
