import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Radio } from 'lucide-react';
import { columns } from './columns';
import type { GrievanceChannel } from './columns';
import {GrievanceChannelViewContent} from './GrievanceChannelViewContent';
interface GrievanceChannelIndexProps {
    data: GrievanceChannel[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'grievance-channels.index',
    create: 'grievance-channels.create',
    edit: 'grievance-channels.edit',
    destroy: 'grievance-channels.destroy',
    bulkDestroy: 'grievance-channels.bulk-destroy',
    export: 'grievance-channels.export',
    import: 'grievance-channels.import',
};

export default function GrievanceChannelIndex({
    data,
    meta,
}: GrievanceChannelIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'is_active',
            title: 'Status',
            options: [
                { label: 'Active', value: 't' },
                { label: 'Inactive', value: 'f' },
            ],
        },
    ];

    return (
        <IndexLayout
            title="Grievance Channels"
            breadcrumbs={[{ label: 'Grievance Channels', icon: Radio }]}
        >
            <div>
                <DataTable<GrievanceChannel, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Grievance Channels"
                    searchPlaceholder="Search by name or code…"
                    resourceLabel="New Channel"
                    defaultSort="name"
                    defaultOrder="asc"
                    getRowLabel={(row) => row.name}
                    viewContent={(row) => (
                        <GrievanceChannelViewContent grievanceChannel={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}
