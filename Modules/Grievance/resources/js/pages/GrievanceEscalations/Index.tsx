import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Users } from 'lucide-react';
import { columns } from './columns';
import type { GrievanceEscalation } from './columns';
import { GrievanceEscalationViewContent } from './GrievanceEscalationViewContent';

interface GrievanceEscalationIndexProps {
    data: GrievanceEscalation[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'grievance-escalations.index',
    create: 'grievance-escalations.create',
    edit: 'grievance-escalations.edit',
    destroy: 'grievance-escalations.destroy',
    bulkDestroy: 'grievance-escalations.bulk-destroy',
    export: 'grievance-escalations.export',
    import: 'grievance-escalations.import',
};

export default function GrievanceEscalationIndex({
    data,
    meta,
}: GrievanceEscalationIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'escalation_level',
            title: 'Level',
            options: [
                { label: 'Level 1 – Zonal Officer', value: '1' },
                { label: 'Level 2 – Regional Head', value: '2' },
                { label: 'Level 3 – Director', value: '3' },
            ],
        },
        {
            id: 'resolved',
            title: 'Resolved',
            options: [
                { label: 'Resolved', value: 't' },
                { label: 'Open', value: 'f' },
            ],
        },
    ];
    // console.log('data', data);

    return (
        <IndexLayout
            title="Grievance Escalation"
            breadcrumbs={[{ label: 'Grievance Escalation', icon: Users }]}
        >
            <div>
                <DataTable<GrievanceEscalation, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Grievance Escalations"
                    description="Manage all Grievance Escalations in your application."
                    searchPlaceholder="Search by reason…"
                    resourceLabel="New Escalation"
                    defaultSort="escalated_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => `Escalation #${row.id}`}
                    viewContent={(row) => (
                        <GrievanceEscalationViewContent
                            GrievanceEscalation={row}
                        />
                    )}
                />
            </div>
        </IndexLayout>
    );
}
