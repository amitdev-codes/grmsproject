// resources/js/Pages/GrievanceStatusHistories/index.tsx
import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { History } from 'lucide-react';
import { columns } from './columns';
import type { GrievanceStatusHistory } from './columns';
import { GrievanceStatusHistoryViewContent } from './GrievanceStatusHistoryViewContent';

interface GrievanceStatusHistoryIndexProps {
    data: GrievanceStatusHistory[];
    meta: PaginationMeta;
    statusOptions: { label: string; value: string }[];
}

// Read-only audit log — no create/edit/destroy routes on purpose.
const routes: DataTableRoutes = {
    index: 'grievance-status-histories.index',
    export: 'grievance-status-histories.export',
};

export default function GrievanceStatusHistoryIndex({
    data,
    meta,
    statusOptions,
}: GrievanceStatusHistoryIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'to_status',
            title: 'To Status',
            options: statusOptions,
        },
        {
            id: 'from_status',
            title: 'From Status',
            options: statusOptions,
        },
    ];

    return (
        <IndexLayout
            title="Grievance Status History"
            breadcrumbs={[{ label: 'Status History', icon: History }]}
        >
            <div>
                <DataTable<GrievanceStatusHistory, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Status History"
                    description="Read-only audit log of every grievance status transition."
                    searchPlaceholder="Search by note…"
                    defaultSort="created_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => `Transition #${row.id}`}
                    viewContent={(row) => (
                        <GrievanceStatusHistoryViewContent history={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}
