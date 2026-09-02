// resources/js/Pages/Resolutions/index.tsx
import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { CheckCircle2 } from 'lucide-react';
import { columns } from './columns';
import type { Resolution } from './columns';
import { ResolutionViewContent } from './ResolutionViewContent';

interface ResolutionIndexProps {
    data: Resolution[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'resolutions.index',
    create: 'resolutions.create',
    edit: 'resolutions.edit',
    destroy: 'resolutions.destroy',
    bulkDestroy: 'resolutions.bulk-destroy',
    export: 'resolutions.export',
    import: 'resolutions.import',
};

export default function ResolutionIndex({ data, meta }: ResolutionIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'state',
            title: 'State',
            options: [
                { label: 'Proposed', value: 'proposed' },
                { label: 'Approved', value: 'approved' },
                { label: 'Confirmed', value: 'confirmed' },
                { label: 'Rejected', value: 'rejected' },
            ],
        },
    ];

    return (
        <IndexLayout
            title="Resolutions"
            breadcrumbs={[{ label: 'Resolutions', icon: CheckCircle2 }]}
        >
            <div>
                <DataTable<Resolution, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Resolutions"
                    description="Track proposed, approved, and confirmed grievance resolutions."
                    searchPlaceholder="Search by resolution text…"
                    resourceLabel="New Resolution"
                    defaultSort="created_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => `Resolution #${row.id}`}
                    viewContent={(row) => (
                        <ResolutionViewContent resolution={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}
