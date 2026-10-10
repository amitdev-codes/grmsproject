import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type { DataTableRoutes, PaginationMeta } from '@/types/data-table';
import { GitBranch } from 'lucide-react';
import { columns } from './columns';
import type { GrievanceWorkflowStep } from './columns';

interface GrievanceWorkflowStepIndexProps {
    data: GrievanceWorkflowStep[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'grievance-workflow-steps.index',
    create: 'grievance-workflow-steps.create',
    edit: 'grievance-workflow-steps.edit',
    destroy: 'grievance-workflow-steps.destroy',
};

export default function GrievanceWorkflowStepIndex({
    data,
    meta,
}: GrievanceWorkflowStepIndexProps) {
    return (
        <IndexLayout
            title="Grievance Workflow"
            breadcrumbs={[{ label: 'Grievance Workflow', icon: GitBranch }]}
        >
            <DataTable<GrievanceWorkflowStep, unknown>
                columns={columns}
                data={data}
                meta={meta}
                routes={routes}
                title="Grievance Workflow"
                description="Configure the review, approval, rejection, and resolution steps used for grievances."
                searchPlaceholder="Search by step or role…"
                resourceLabel="New Workflow Step"
                defaultSort="step_number"
                defaultOrder="asc"
                getRowLabel={(row) => `Level ${row.step_number}: ${row.name}`}
            />
        </IndexLayout>
    );
}
