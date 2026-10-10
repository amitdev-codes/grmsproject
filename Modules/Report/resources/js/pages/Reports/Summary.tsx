import { ReportFilters } from './ReportFilters';
import type { ReportFilterOptions, ReportFilterValues } from './ReportFilters';
import { ReportLayout } from './ReportLayout';
import { ReportTable } from './ReportTable';
import type { ReportRow } from './ReportTable';

interface SummaryReportProps {
    rows: ReportRow[];
    groupBy: string;
    filters: ReportFilterValues;
    options: ReportFilterOptions;
}

const countColumns = [
    { key: 'total', label: 'Total lodged', align: 'right' as const },
    { key: 'pending', label: 'Pending', align: 'right' as const },
    { key: 'resolved', label: 'Resolved', align: 'right' as const },
];

export default function Summary({
    rows,
    groupBy,
    filters,
    options,
}: SummaryReportProps) {
    const groupLabels: Record<string, string> = {
        year: 'Year',
        month: 'Month',
        division: 'Division',
        category: 'Category type',
    };

    return (
        <ReportLayout
            title="Summary"
            description="Filter grievance totals and group them by year, month, division, or category type."
        >
            <ReportFilters
                options={options}
                filters={filters}
                groupingOptions={[
                    { value: 'year', label: 'Year' },
                    { value: 'month', label: 'Month' },
                    { value: 'division', label: 'Division' },
                    { value: 'category', label: 'Category type' },
                ]}
            />
            <ReportTable
                title={`${groupLabels[groupBy] ?? 'Year'} summary`}
                columns={[
                    { key: 'group', label: groupLabels[groupBy] ?? 'Year' },
                    ...countColumns,
                ]}
                rows={rows}
            />
        </ReportLayout>
    );
}
