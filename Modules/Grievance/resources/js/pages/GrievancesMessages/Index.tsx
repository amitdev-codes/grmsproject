import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { MessageSquare } from 'lucide-react';
import { columns } from './columns';
import type { GrievanceMessage } from './columns';
import { GrievanceMessageViewContent } from './GrievanceMessageViewContent';

interface GrievanceMessageIndexProps {
    data: GrievanceMessage[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'grievance-messages.index',
    create: 'grievance-messages.create',
    edit: 'grievance-messages.edit',
    destroy: 'grievance-messages.destroy',
    bulkDestroy: 'grievance-messages.bulk-destroy',
    export: 'grievance-messages.export',
    import: 'grievance-messages.import',
};

export default function GrievanceMessageIndex({
    data,
    meta,
}: GrievanceMessageIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'sender_type',
            title: 'Sender',
            options: [
                { label: 'Officer', value: 'officer' },
                { label: 'Complainant', value: 'complainant' },
                { label: 'System', value: 'system' },
            ],
        },
        {
            id: 'is_internal',
            title: 'Visibility',
            options: [
                { label: 'Internal', value: 't' },
                { label: 'Visible to complainant', value: 'f' },
            ],
        },
    ];

    return (
        <IndexLayout
            title="Grievance Messages"
            breadcrumbs={[{ label: 'Grievance Messages', icon: MessageSquare }]}
        >
            <div>
                <DataTable<GrievanceMessage, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Grievance Messages"
                    description="Manage all messages exchanged on grievances."
                    searchPlaceholder="Search by message content…"
                    resourceLabel="New Message"
                    defaultSort="created_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => `Message #${row.id}`}
                    viewContent={(row) => (
                        <GrievanceMessageViewContent grievanceMessage={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}
