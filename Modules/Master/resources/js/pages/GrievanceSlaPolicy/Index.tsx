import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Timer } from 'lucide-react';
import { Columns } from './Columns';
import type { GrievanceSlaPolicy } from './Columns';
import { GrievanceSlaPolicyViewContent } from './GrievanceSlaPolicyViewContent';

interface GrievanceSlaPolicyIndexProps {
    data: GrievanceSlaPolicy[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'grievance-sla-policies.index',
    create: 'grievance-sla-policies.create',
    edit: 'grievance-sla-policies.edit',
    destroy: 'grievance-sla-policies.destroy',
    bulkDestroy: 'grievance-sla-policies.bulk-destroy',
    export: 'grievance-sla-policies.export',
    import: 'grievance-sla-policies.import',
};

export default function GrievanceSlaPolicyIndex({ data, meta }: GrievanceSlaPolicyIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'priority',
            title: 'Priority',
            options: [
                { label: 'Low', value: 'low' },
                { label: 'Medium', value: 'medium' },
                { label: 'High', value: 'high' },
                { label: 'Critical', value: 'critical' },
            ],
        },
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
            title="SLA Policies"
            breadcrumbs={[{ label: 'SLA Policies', icon: Timer }]}
        >
            <div>
                <DataTable<GrievanceSlaPolicy, unknown>
                    columns={Columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="SLA Policies"
                    description="Manage all SLA policies in your application."
                    searchPlaceholder="Search by code or name…"
                    resourceLabel="New SLA Policy"
                    defaultSort="name"
                    defaultOrder="asc"
                    getRowLabel={(row) => row.name}
                    viewContent={(row) => (
                        <GrievanceSlaPolicyViewContent policy={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}