import { ReportFilters } from './ReportFilters';
import type { ReportFilterOptions, ReportFilterValues } from './ReportFilters';
import { ReportLayout } from './ReportLayout';
import { ReportTable } from './ReportTable';
import type { ReportRow } from './ReportTable';

export default function Annex({
    byCategory,
    filters,
    options,
}: {
    byCategory: ReportRow[];
    filters: ReportFilterValues;
    options: ReportFilterOptions;
}) {
    return (
        <ReportLayout
            title="Annex"
            description="Category-wise grievance totals, including current pending and resolved cases."
        >
            <ReportFilters options={options} filters={filters} />
            <ReportTable
                title="Grievances by category type"
                columns={[
                    { key: 'category', label: 'Category type' },
                    { key: 'total', label: 'Total lodged', align: 'right' },
                    { key: 'pending', label: 'Pending', align: 'right' },
                    { key: 'resolved', label: 'Resolved', align: 'right' },
                ]}
                rows={byCategory}
            />
        </ReportLayout>
    );
}
