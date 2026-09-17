import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { ArrowUpRight } from 'lucide-react';
import { Columns } from './Columns';
import type { GrievanceEscalationRule } from './Columns';
import { GrievanceEscalationRuleViewContent } from './GrievanceEscalationRuleViewContent';

interface GrievanceEscalationRuleIndexProps {
    data: GrievanceEscalationRule[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'grievance-escalation-rules.index',
    create: 'grievance-escalation-rules.create',
    edit: 'grievance-escalation-rules.edit',
    destroy: 'grievance-escalation-rules.destroy',
    bulkDestroy: 'grievance-escalation-rules.bulk-destroy',
    export: 'grievance-escalation-rules.export',
    import: 'grievance-escalation-rules.import',
};

export default function GrievanceEscalationRuleIndex({ data, meta }: GrievanceEscalationRuleIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'escalation_level',
            title: 'Level',
            options: [1, 2, 3, 4, 5].map((l) => ({ label: `Level ${l}`, value: String(l) })),
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
            title="Escalation Rules"
            breadcrumbs={[{ label: 'Escalation Rules', icon: ArrowUpRight }]}
        >
            <div>
                <DataTable<GrievanceEscalationRule, unknown>
                    columns={Columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Escalation Rules"
                    description="Manage all escalation rules in your application."
                    searchPlaceholder="Search by target role…"
                    resourceLabel="New Escalation Rule"
                    defaultSort="escalation_level"
                    defaultOrder="asc"
                    getRowLabel={(row) => `Level ${row.escalation_level}`}
                    viewContent={(row) => (
                        <GrievanceEscalationRuleViewContent rule={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}